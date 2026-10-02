<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RecapController extends Controller
{
    public function index()
    {
        return response()->json([
            'total_facilities' => Facility::count(),
            'total_reservations' => Reservation::whereMonth('created_at', now()->month)->count(),
            'total_reports' => Report::count(),
            'broken_facilities' => Facility::where('status', 'maintenance')->count(),
        ]);
    }

    public function reports(Request $request)
    {
        $filters = $request->validate([
            'status' => 'nullable|in:new,in_progress,resolved,rejected',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $baseQuery = Report::query();
        $counts = (clone $baseQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $reports = $baseQuery
            ->with([
                'facility:id,name,code,status',
                'user:id,name,email',
                'handler:id,name',
                'photos:id,report_id,file_path',
            ])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest('id')
            ->limit($filters['limit'] ?? 20)
            ->get();

        return response()->json([
            'data' => $reports,
            'counts' => [
                'all' => $counts->sum(),
                'new' => (int) ($counts['new'] ?? 0),
                'in_progress' => (int) ($counts['in_progress'] ?? 0),
                'resolved' => (int) ($counts['resolved'] ?? 0),
                'rejected' => (int) ($counts['rejected'] ?? 0),
            ],
        ]);
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->validate([
            'status' => 'nullable|in:new,in_progress,resolved,rejected',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
        ]);

        $query = Report::query()
            ->with(['facility:id,name', 'user:id,name'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->orderBy('id');

        $csvFileName = 'rekap_laporan_kerusakan_'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // BOM membuat karakter UTF-8 (termasuk nama Indonesia) terbaca benar di Excel.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID', 'Pelapor', 'Fasilitas', 'Kategori', 'Status', 'Tanggal'], ',', '"', '\\');

            foreach ($query->lazyById() as $report) {
                $values = [
                    $report->id,
                    $report->user?->name,
                    $report->facility?->name,
                    $report->category,
                    $report->status,
                    $report->created_at?->format('Y-m-d'),
                ];

                // Cegah nilai buatan pengguna dieksekusi sebagai formula spreadsheet.
                $values = array_map(static function ($value) {
                    $value = (string) ($value ?? '');
                    return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
                }, $values);

                fputcsv($handle, $values, ',', '"', '\\');
            }

            fclose($handle);
        }, $csvFileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
