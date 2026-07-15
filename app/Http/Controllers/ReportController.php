<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelPdf\Facades\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user');

        // Filter tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Data tabel
        $orders = $query
            ->latest()
            ->get();

        // Summary Card (mengikuti filter tanggal)
        $totalRevenue = (clone $query)->sum('total_amount');
        $totalOrders = (clone $query)->count();

        // Booking & Customer (belum difilter tanggal)
        $totalBookings = Booking::count();
        $totalCustomers = User::where('role', 'Pelanggan')->count();

        // Grafik Pendapatan Bulanan
        $monthlySales = Order::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        // Siapkan wadah untuk 12 bulan (Jan - Des) dengan nilai awal 0
        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $chartValues = array_fill(0, 12, 0); // Isian default [0, 0, 0, ..., 0]

        // Masukkan data transaksi riil ke bulan yang sesuai
        foreach ($monthlySales as $sale) {
            // Nilai $sale->month (1 - 12) disesuaikan ke indeks array (0 - 11)
            $chartValues[$sale->month - 1] = (int) $sale->total;
        }

        return view('pages.reports.index', compact(
            'orders',
            'totalRevenue',
            'totalOrders',
            'totalBookings',
            'totalCustomers',
            'monthlySales', // <-- Kunci perbaikannya ada di baris ini!
            'chartLabels',  
            'chartValues'
        ));
    }

    /**
     * Download laporan penjualan dalam format PDF
     */
    public function downloadPdf()
    {
        $orders = Order::with('user')
            ->latest()
            ->get();

        return Pdf::view('pdf.report', [
            'orders' => $orders,
        ])->download('Laporan-Penjualan.pdf');
    }
}