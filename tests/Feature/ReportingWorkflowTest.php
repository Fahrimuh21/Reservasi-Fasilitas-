<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $otherUser;

    private User $officer;

    private User $admin;

    private Facility $facility;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 2)->setTime(9, 0));
        $this->user = User::factory()->create(['name' => 'Pelapor Utama']);
        $this->otherUser = User::factory()->create(['name' => 'Pelapor Lain']);
        $this->officer = User::factory()->officer()->create();
        $this->admin = User::factory()->admin()->create();
        $type = DB::table('facility_types')->insertGetId(['name' => 'Laboratorium']);
        $location = DB::table('locations')->insertGetId(['name' => 'Lab 1', 'building' => 'Gedung A', 'floor' => '1']);
        $this->facility = Facility::create([
            'code' => 'LAB-01',
            'name' => 'Laboratorium Komputer',
            'facility_type_id' => $type,
            'location_id' => $location,
            'capacity' => 30,
            'status' => 'active',
        ]);
    }

    private function createReport(User $reporter, array $overrides = []): Report
    {
        return Report::create(array_merge([
            'reporter_id' => $reporter->id,
            'facility_id' => $this->facility->id,
            'category' => 'Elektronik',
            'description' => 'Proyektor tidak menyala',
            'status' => 'new',
        ], $overrides));
    }

    public function test_report_input_is_validated_and_users_only_see_their_own_reports(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/user/reports', [
            'facility_id' => 999999,
            'category' => '',
            'description' => '',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['facility_id', 'category', 'description']);

        $mine = $this->createReport($this->user);
        $this->createReport($this->otherUser, ['description' => 'Tidak boleh terlihat']);

        $this->getJson('/api/user/reports')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id);
    }

    public function test_report_routes_enforce_authentication_and_roles(): void
    {
        $report = $this->createReport($this->user);

        $this->getJson('/api/user/reports')->assertUnauthorized();
        $this->putJson("/api/officer/reports/{$report->id}", ['status' => 'in_progress'])->assertUnauthorized();
        // Download browser tidak selalu mengirim Accept: application/json.
        $this->get('/api/admin/export/reports')->assertUnauthorized()->assertJsonPath('message', 'Unauthenticated.');

        Sanctum::actingAs($this->user);
        $this->getJson('/api/officer/reports')->assertForbidden();
        $this->putJson("/api/officer/reports/{$report->id}", ['status' => 'in_progress'])->assertForbidden();
        $this->getJson('/api/admin/export/reports')->assertForbidden();

        Sanctum::actingAs($this->officer);
        $this->getJson('/api/admin/export/reports')->assertForbidden();
    }

    public function test_only_allowed_report_transitions_are_saved(): void
    {
        $report = $this->createReport($this->user);
        Sanctum::actingAs($this->officer);

        $this->putJson("/api/officer/reports/{$report->id}", ['status' => 'resolved'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'new', 'handled_by' => null]);

        $this->putJson("/api/officer/reports/{$report->id}", ['status' => 'in_progress'])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.facility.status', 'maintenance');
        $this->putJson("/api/officer/reports/{$report->id}", [
            'status' => 'resolved',
            'resolution_note' => 'Kabel daya diganti',
        ])->assertOk()->assertJsonPath('data.status', 'resolved');

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'resolved',
            'resolution_note' => 'Kabel daya diganti',
            'handled_by' => $this->officer->id,
        ]);
        $this->assertDatabaseHas('facilities', ['id' => $this->facility->id, 'status' => 'active']);

        $this->putJson("/api/officer/reports/{$report->id}", ['status' => 'in_progress'])
            ->assertUnprocessable();
    }

    public function test_resolving_a_report_does_not_override_manual_maintenance(): void
    {
        $report = $this->createReport($this->user);
        $this->facility->update(['status' => 'maintenance']);
        DB::table('facility_status_logs')->insert([
            'facility_id' => $this->facility->id,
            'old_status' => 'active',
            'new_status' => 'maintenance',
            'related_report_id' => null,
            'changed_by' => $this->officer->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Sanctum::actingAs($this->officer);

        $this->putJson("/api/officer/reports/{$report->id}", ['status' => 'in_progress'])->assertOk();
        $this->putJson("/api/officer/reports/{$report->id}", ['status' => 'resolved'])->assertOk();

        $this->assertDatabaseHas('facilities', ['id' => $this->facility->id, 'status' => 'maintenance']);
    }

    public function test_admin_export_is_downloadable_utf8_csv_and_applies_filters(): void
    {
        $included = $this->createReport($this->user, [
            'category' => 'AC, HVAC',
            'description' => 'Termasuk export',
            'status' => 'resolved',
        ]);
        $this->createReport($this->otherUser, ['category' => 'Listrik', 'status' => 'new']);
        Sanctum::actingAs($this->admin);

        $response = $this->get('/api/admin/export/reports?status=resolved&date_from=2026-10-01&date_to=2026-10-02');
        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertDownload('rekap_laporan_kerusakan_2026-10-02.csv');

        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('ID,Pelapor,Fasilitas,Kategori,Status,Tanggal', $content);
        $this->assertStringContainsString((string) $included->id.',"Pelapor Utama","Laboratorium Komputer","AC, HVAC",resolved,2026-10-02', $content);
        $this->assertStringNotContainsString('Pelapor Lain', $content);
    }

    public function test_admin_can_export_an_empty_result_and_invalid_filters_are_rejected(): void
    {
        Sanctum::actingAs($this->admin);

        $empty = $this->get('/api/admin/export/reports?status=rejected');
        $empty->assertOk();
        $this->assertSame("\xEF\xBB\xBFID,Pelapor,Fasilitas,Kategori,Status,Tanggal\n", $empty->streamedContent());

        $this->getJson('/api/admin/export/reports?status=unknown')->assertUnprocessable();
        $this->getJson('/api/admin/export/reports?date_from=2026-10-03&date_to=2026-10-02')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date_to');
    }

    public function test_admin_report_feed_returns_live_database_data_and_status_counts(): void
    {
        $newReport = $this->createReport($this->user, ['description' => 'Data laporan terbaru']);
        $resolvedReport = $this->createReport($this->otherUser, [
            'status' => 'resolved',
            'handled_by' => $this->officer->id,
            'resolution_note' => 'Sudah diperbaiki',
        ]);
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/admin/reports')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('counts.all', 2)
            ->assertJsonPath('counts.new', 1)
            ->assertJsonPath('counts.resolved', 1)
            ->assertJsonPath('data.0.id', $resolvedReport->id)
            ->assertJsonPath('data.0.handler.id', $this->officer->id);

        $this->getJson('/api/admin/reports?status=new')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $newReport->id);

        Sanctum::actingAs($this->user);
        $this->getJson('/api/admin/reports')->assertForbidden();
    }
}
