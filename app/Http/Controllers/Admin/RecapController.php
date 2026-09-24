<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;

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

    public function exportCsv()
    {
        $reports = Report::with('facility', 'user')->get();
        $csvFileName = 'rekap_laporan_kerusakan.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['ID', 'Pelapor', 'Fasilitas', 'Kategori', 'Status', 'Tanggal']);

        foreach ($reports as $report) {
            fputcsv($handle, [
                $report->id,
                $report->user->name,
                $report->facility->name,
                $report->category,
                $report->status,
                $report->created_at->format('Y-m-d')
            ]);
        }

        fclose($handle);

        return response()->stream(function () use ($handle) {}, 200, $headers);
    }
}