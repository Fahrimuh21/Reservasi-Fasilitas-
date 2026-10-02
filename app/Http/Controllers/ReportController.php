<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReportController extends Controller
{
    public function userIndex(Request $request)
    {
        $reports = Report::with(['facility:id,name', 'photos'])
            ->where('reporter_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $reports,
        ]);
    }

    // User: Submit Laporan Kerusakan
    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'category' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048', // Validasi multiple foto
        ]);

        $report = DB::transaction(function () use ($request) {
            $report = Report::create([
                'reporter_id' => $request->user()->id,
                'facility_id' => $request->facility_id,
                'category' => $request->category,
                'description' => $request->description,
                'status' => 'new',
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('reports', 'public');
                    $report->photos()->create(['file_path' => $path]);
                }
            }

            return $report;
        });

        return response()->json(['message' => 'Laporan berhasil dikirim', 'data' => $report], 201);
    }

    public function officerIndex(Request $request)
    {
        $query = Report::with(['facility:id,name,status', 'user:id,name,email', 'photos'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    // Officer: Proses Laporan & Sinkronisasi Maintenance
    public function updateStatus(Request $request, Report $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:in_progress,resolved,rejected',
            'resolution_note' => 'nullable|string|max:1000',
        ]);

        try {
            $updatedReport = DB::transaction(function () use ($request, $report, $validated) {
                $facility = Facility::whereKey($report->facility_id)->lockForUpdate()->firstOrFail();
                $lockedReport = Report::whereKey($report->id)->lockForUpdate()->firstOrFail();
                $allowed = ['new' => ['in_progress', 'rejected'], 'in_progress' => ['resolved']];

                if (! in_array($validated['status'], $allowed[$lockedReport->status] ?? [], true)) {
                    throw ValidationException::withMessages([
                        'status' => 'Laporan sudah diproses atau transisi status tidak valid.',
                    ]);
                }

                $lockedReport->update([
                    'status' => $validated['status'],
                    'resolution_note' => $validated['resolution_note'] ?? null,
                    'handled_by' => $request->user()->id,
                ]);

                $newStatus = $facility->status;
                if ($validated['status'] === 'in_progress' && $facility->isActive()) {
                    $newStatus = 'maintenance';
                } elseif ($validated['status'] === 'resolved'
                    && $facility->status === 'maintenance'
                    && ! Report::where('facility_id', $facility->id)->where('status', 'in_progress')->exists()
                    && DB::table('facility_status_logs')
                        ->where('facility_id', $facility->id)
                        ->latest('id')
                        ->value('related_report_id') !== null) {
                    // Jangan menimpa maintenance manual. Hanya pulihkan fasilitas bila
                    // transisi maintenance terakhir berasal dari alur laporan.
                    $newStatus = 'active';
                }

                if ($newStatus !== $facility->status) {
                    DB::table('facility_status_logs')->insert([
                        'facility_id' => $facility->id,
                        'old_status' => $facility->status,
                        'new_status' => $newStatus,
                        'related_report_id' => $lockedReport->id,
                        'changed_by' => $request->user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $facility->update(['status' => $newStatus]);
                }

                return $lockedReport->load(['facility:id,name,status', 'user:id,name,email', 'photos']);
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Gagal memperbarui status laporan.', [
                'report_id' => $report->id,
                'officer_id' => $request->user()?->id,
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => 'Server tidak dapat menyimpan perubahan laporan. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'message' => 'Status laporan dan fasilitas diperbarui.',
            'data' => $updatedReport,
        ]);
    }
}
