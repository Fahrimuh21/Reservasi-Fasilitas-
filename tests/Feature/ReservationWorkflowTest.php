<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReservationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $officer;

    private Facility $facility;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 9, 24)->setTime(8, 0));
        $this->user = User::factory()->create();
        $this->officer = User::factory()->officer()->create();
        $type = DB::table('facility_types')->insertGetId(['name' => 'Ruang Rapat']);
        $location = DB::table('locations')->insertGetId(['name' => 'Ruang 1', 'building' => 'Gedung A', 'floor' => '1']);
        $this->facility = Facility::create([
            'code' => 'TEST-01', 'name' => 'Ruang Pengujian', 'facility_type_id' => $type,
            'location_id' => $location, 'capacity' => 20, 'status' => 'active',
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'facility_id' => $this->facility->id, 'start_at' => '2026-09-25 09:00:00',
            'end_at' => '2026-09-25 10:00:00', 'purpose' => 'Rapat organisasi',
        ], $overrides);
    }

    private function requestReservation(): int
    {
        Sanctum::actingAs($this->user);

        return $this->postJson('/api/reservations', $this->payload())->assertCreated()->json('data.id');
    }

    public function test_request_is_persisted_then_approved_and_visible_to_its_owner(): void
    {
        $id = $this->requestReservation();
        $this->assertDatabaseHas('reservations', ['id' => $id, 'user_id' => $this->user->id, 'status' => 'pending']);
        Sanctum::actingAs($this->officer);
        $this->getJson('/api/officer/reservations')->assertOk()->assertJsonPath('data.0.id', $id);
        $this->patchJson("/api/officer/reservations/$id/approve", ['decision_note' => 'Disetujui untuk rapat'])
            ->assertOk()->assertJsonPath('data.status', 'approved');
        $this->assertDatabaseHas('reservations', ['id' => $id, 'status' => 'approved', 'handled_by' => $this->officer->id]);
        $this->assertNotNull(Reservation::findOrFail($id)->handled_at);
        Sanctum::actingAs($this->user);
        $this->getJson('/api/reservations')->assertOk()->assertJsonPath('data.0.status', 'approved')
            ->assertJsonPath('data.0.decision_note', 'Disetujui untuk rapat')
            ->assertJsonPath('data.0.start_at', '2026-09-25T02:00:00.000000Z');
    }

    public function test_rejection_is_saved_with_note_and_cannot_be_overwritten(): void
    {
        $id = $this->requestReservation();
        Sanctum::actingAs($this->officer);
        $this->patchJson("/api/officer/reservations/$id/reject", ['decision_note' => 'Keperluan tidak sesuai'])
            ->assertOk()->assertJsonPath('data.status', 'rejected');
        $this->patchJson("/api/officer/reservations/$id/approve")->assertUnprocessable();
        $this->assertDatabaseHas('reservations', ['id' => $id, 'status' => 'rejected', 'decision_note' => 'Keperluan tidak sesuai']);
    }

    public function test_officer_can_cancel_an_approved_reservation_with_reason(): void
    {
        $id = $this->requestReservation();
        Sanctum::actingAs($this->officer);
        $this->patchJson("/api/officer/reservations/$id/approve")
            ->assertOk();

        $this->patchJson("/api/officer/reservations/$id/cancel", [
            'cancellation_reason' => 'Fasilitas mendadak tidak dapat digunakan.',
        ])->assertOk()->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('reservations', [
            'id' => $id,
            'status' => 'cancelled',
            'cancelled_by' => $this->officer->id,
            'cancellation_reason' => 'Fasilitas mendadak tidak dapat digunakan.',
        ]);
    }

    public function test_cancellation_releases_slot_and_cannot_be_approved(): void
    {
        $id = $this->requestReservation();
        $this->getJson("/api/facilities/{$this->facility->id}/availability?date=2026-09-25")
            ->assertOk()->assertJsonPath('data.slots.4.status', 'terisi');
        $this->postJson("/api/reservations/$id/cancel", ['reason' => 'Jadwal berubah'])->assertOk();
        $this->assertDatabaseHas('reservations', ['id' => $id, 'status' => 'cancelled', 'cancelled_by' => $this->user->id]);
        $this->getJson("/api/facilities/{$this->facility->id}/availability?date=2026-09-25")
            ->assertOk()->assertJsonPath('data.slots.4.status', 'tersedia');
        Sanctum::actingAs($this->officer);
        $this->patchJson("/api/officer/reservations/$id/approve")->assertUnprocessable();
    }

    public function test_availability_marks_only_past_slots_as_unavailable_without_four_hour_cutoff(): void
    {
        $this->getJson("/api/facilities/{$this->facility->id}/availability?date=2026-09-24")
            ->assertOk()
            ->assertJsonPath('data.slots.1.status', 'terisi')
            ->assertJsonPath('data.slots.2.status', 'tersedia');
    }

    public function test_overlap_is_rejected_but_adjacent_slot_is_allowed(): void
    {
        $this->requestReservation();
        $this->postJson('/api/reservations', $this->payload(['start_at' => '2026-09-25 09:30:00']))->assertUnprocessable();
        $this->postJson('/api/reservations', $this->payload(['start_at' => '2026-09-25 10:00:00', 'end_at' => '2026-09-25 11:00:00']))->assertCreated();
        $this->assertDatabaseCount('reservations', 2);
    }

    public function test_approval_checks_facility_and_existing_approved_schedule(): void
    {
        $id = $this->requestReservation();
        $this->facility->update(['status' => 'maintenance']);
        Sanctum::actingAs($this->officer);
        $this->patchJson("/api/officer/reservations/$id/approve")->assertUnprocessable();
        $this->facility->update(['status' => 'active']);
        Reservation::create(array_merge($this->payload(), ['user_id' => $this->user->id, 'status' => 'approved']));
        $this->patchJson("/api/officer/reservations/$id/approve")->assertUnprocessable();
        $this->assertDatabaseHas('reservations', ['id' => $id, 'status' => 'pending']);
    }

    public function test_invalid_times_and_inactive_facilities_are_not_saved(): void
    {
        Sanctum::actingAs($this->user);
        foreach ([
            ['start_at' => '2026-09-23 09:00:00', 'end_at' => '2026-09-23 10:00:00'],
            ['start_at' => '2026-09-25 06:30:00'],
            ['start_at' => '2026-09-25 09:15:00'],
            ['start_at' => '2026-09-25 09:00:01'],
            ['end_at' => '2026-09-26 10:00:00'],
            ['end_at' => '2026-09-25 08:00:00'],
        ] as $invalid) {
            $this->postJson('/api/reservations', $this->payload($invalid))->assertUnprocessable();
        }
        $this->facility->update(['status' => 'inactive']);
        $this->postJson('/api/reservations', $this->payload())->assertUnprocessable();
        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_roles_and_ownership_are_enforced(): void
    {
        $this->postJson('/api/reservations', $this->payload())->assertUnauthorized();
        $id = $this->requestReservation();
        $this->patchJson("/api/officer/reservations/$id/approve")->assertForbidden();
        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/reservations')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/reservations/$id")->assertForbidden();
        $this->postJson("/api/reservations/$id/cancel", ['reason' => 'Test'])->assertUnprocessable();
        foreach ([$this->officer, User::factory()->admin()->create()] as $staff) {
            Sanctum::actingAs($staff);
            $this->postJson('/api/reservations', $this->payload())->assertForbidden();
        }
        $this->patchJson("/api/officer/reservations/$id/approve")->assertForbidden();
    }

    public function test_reports_are_persisted_processed_and_visible_to_owner(): void
    {
        Sanctum::actingAs($this->user);
        $id = $this->postJson('/api/user/reports', [
            'facility_id' => $this->facility->id, 'category' => 'Elektronik', 'description' => 'Proyektor tidak menyala',
        ])->assertCreated()->json('data.id');
        $this->assertDatabaseHas('reports', ['id' => $id, 'reporter_id' => $this->user->id, 'status' => 'new']);
        Sanctum::actingAs($this->officer);
        $this->getJson('/api/officer/reports')->assertOk()->assertJsonPath('data.0.user.id', $this->user->id);
        $this->putJson("/api/officer/reports/$id", ['status' => 'in_progress'])->assertOk();
        $this->assertDatabaseHas('facilities', ['id' => $this->facility->id, 'status' => 'maintenance']);
        $this->putJson("/api/officer/reports/$id", ['status' => 'resolved', 'resolution_note' => 'Kabel diganti'])->assertOk();
        $this->assertDatabaseHas('facility_status_logs', ['related_report_id' => $id, 'new_status' => 'active', 'changed_by' => $this->officer->id]);
        Sanctum::actingAs($this->user);
        $this->getJson('/api/user/reports')->assertOk()->assertJsonPath('data.0.status', 'resolved')->assertJsonPath('data.0.resolution_note', 'Kabel diganti');
    }

    public function test_report_photo_is_stored_and_returned_to_officer(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->user);
        $photo = UploadedFile::fake()->createWithContent('kerusakan.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aS1sAAAAASUVORK5CYII='));
        $this->postJson('/api/user/reports', [
            'facility_id' => $this->facility->id, 'category' => 'Elektronik', 'description' => 'Foto kerusakan', 'photos' => [$photo],
        ])->assertCreated();
        Sanctum::actingAs($this->officer);
        $path = $this->getJson('/api/officer/reports')->assertOk()->json('data.0.photos.0.file_path');
        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseHas('report_photos', ['file_path' => $path]);
    }
}
