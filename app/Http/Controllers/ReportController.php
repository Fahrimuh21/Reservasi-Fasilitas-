<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Facility;
use App\Models\FacilityStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // User: Submit Laporan Kerusakan
    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'category' => 'required|string',
            'description' => 'required|string',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048' // Validasi multiple foto
        ]);

        DB::transaction(function () use ($request) {
            $report = Report::create([
                'user_id' => $request->user()->id,
                'facility_id' => $request->facility_id,
                'category' => $request->category,
                'description' => $request->description,
                'status' => 'new'
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('reports', 'public');
                    $report->photos()->create(['photo_path' => $path]);
                }
            }
        });

        return response()->json(['message' => 'Laporan berhasil dikirim'], 201);
    }

    // Officer: Proses Laporan & Sinkronisasi Maintenance
    public function updateStatus(Request $request, Report $report)
    {
        $request->validate([
            'status' => 'required|in:in_progress,resolved,rejected',
            'resolution_note' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request, $report) {
            $report->update([
                'status' => $request->status,
                'resolution_note' => $request->resolution_note
            ]);

            $facility = $report->facility;

            // Jika diproses, fasilitas masuk masa perbaikan
            if ($request->status === 'in_progress') {
                $facility->update(['status' => 'maintenance']);
                FacilityStatusLog::create(['facility_id' => $facility->id, 'status' => 'maintenance', 'notes' => 'Terkait laporan ID ' . $report->id]);
            } 
            // Jika selesai/ditolak, fasilitas kembali aktif
            elseif (in_array($request->status, ['resolved', 'rejected'])) {
                $facility->update(['status' => 'active']);
                FacilityStatusLog::create(['facility_id' => $facility->id, 'status' => 'active', 'notes' => 'Perbaikan selesai/dibatalkan']);
            }
        });

        return response()->json(['message' => 'Status laporan dan fasilitas diperbarui']);
    }
}