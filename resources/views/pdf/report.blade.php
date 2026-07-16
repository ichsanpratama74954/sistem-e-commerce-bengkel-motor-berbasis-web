<?php
use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Order;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;

new class extends Component {
    
    #[Computed]
    public function stats()
    {
        // 1. Hitung pendapatan dari Order yang selesai
        $orderRevenue = Order::where('status', 'completed')->sum('total_price');

        // 2. Hitung pendapatan dari Booking yang selesai (mengambil harga dari relasi tabel services)
        $bookingRevenue = Booking::join('services', 'bookings.service_id', '=', 'services.id')
            ->where('bookings.status', 'completed') // Sesuaikan status ini ('completed' / 'success' / 'selesai')
            ->sum('services.service_price');

        // 3. Total Gabungan Pendapatan
        $totalPendapatan = $orderRevenue + $bookingRevenue;

        return [
            'total_pendapatan' => $totalPendapatan,
            'total_order'      => Order::count(),
            'total_booking'    => Booking::count(),
            'total_customer'   => User::where('role', 'customer')->count(),
        ];
    }

    #[Computed]
    public function chartData()
    {
        $monthlyRevenue = [];
        $currentYear = date('Y');

        // Melakukan looping dari bulan 1 (Jan) sampai 12 (Des) untuk mengisi grafik
        for ($month = 1; $month <= 12; $month++) {
            // Pendapatan order per bulan
            $orderTotal = Order::where('status', 'completed')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $currentYear)
                ->sum('total_price');

            // Pendapatan booking per bulan
            $bookingTotal = Booking::join('services', 'bookings.service_id', '=', 'services.id')
                ->where('bookings.status', 'completed')
                ->whereMonth('bookings.created_at', $month)
                ->whereYear('bookings.created_at', $currentYear)
                ->sum('services.service_price');

            // Gabungan pendapatan bulanan
            $monthlyRevenue[] = $orderTotal + $bookingTotal;
        }

        return $monthlyRevenue;
    }

    public function downloadPdf()
    {
        // Logika cetak PDF Laporan Anda
        session()->flash('success', 'Fitur cetak PDF sedang diproses...');
    }
}; ?>

<div class="max-w-7xl mx-auto space-y-8 container pb-16 px-4 sm:px-6">
    
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-zinc-950 dark:text-white tracking-tight">Laporan Penjualan</h1>
            <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-1">Pantau performa penjualan dan transaksi bengkel motor Anda secara real-time.</p>
        </div>
        <div>
            <button wire:click="downloadPdf" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md transition-all active:scale-[0.98]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </button>
        </div>
    </div>

    {{-- KARTU STATISTIK UTAMA --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-sm flex items-center justify-between">
            <div class="space-y-2">
                <p class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Total Pendapatan</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                    Rp {{ number_format($this->stats['total_pendapatan'], 0, ',', '.') }}
                </p>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-sm flex items-center justify-between">
            <div class="space-y-2">
                <p class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Total Order</p>
                <p class="text-3xl font-black text-zinc-950 dark:text-white">
                    {{ $this->stats['total_order'] }}
                </p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-sm flex items-center justify-between">
            <div class="space-y-2">
                <p class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Total Booking</p>
                <p class="text-3xl font-black text-zinc-950 dark:text-white">
                    {{ $this->stats['total_booking'] }}
                </p>
            </div>
            <div class="p-3 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-sm flex items-center justify-between">
            <div class="space-y-2">
                <p class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Total Customer</p>
                <p class="text-3xl font-black text-zinc-950 dark:text-white">
                    {{ $this->stats['total_customer'] }}
                </p>
            </div>
            <div class="p-3 bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

    </div>

    {{-- GRAFIK PENDAPATAN BULANAN --}}
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-100 dark:border-zinc-800 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-zinc-950 dark:text-white">Grafik Pendapatan Bulanan</h3>
            <span class="text-xs font-semibold px-2.5 py-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 rounded-md">Jan - Des</span>
        </div>

        {{-- Visual Chart Menggunakan Render Bar CSS Modern Tailwind --}}
        <div class="h-64 flex items-end gap-2 pt-4 border-b border-zinc-100 dark:border-zinc-800/80 px-2">
            @php 
                $maxRevenue = max($this->chartData) > 0 ? max($this->chartData) : 1; 
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            @endphp
            
            @foreach($this->chartData as $index => $amount)
                @php 
                    // Hitung persentase tinggi grafik batang secara dinamis
                    $percentage = ($amount / $maxRevenue) * 100; 
                @endphp
                <div class="flex-1 flex flex-col items-center h-full justify-end group relative">
                    <div class="absolute bottom-full mb-2 hidden group-hover:flex flex-col items-center z-10">
                        <span class="bg-zinc-950 text-white text-[10px] font-bold px-2 py-1 rounded shadow-md whitespace-nowrap">
                            Rp {{ number_format($amount, 0, ',', '.') }}
                        </span>
                        <div class="w-1.5 h-1.5 bg-zinc-950 rotate-45 -mt-1"></div>
                    </div>
                    
                    <div style="height: {{ $percentage }}%; min-height: {{ $amount > 0 ? '4px' : '0px' }};" 
                         class="w-full bg-blue-500 hover:bg-blue-600 rounded-t-md transition-all duration-300 shadow-inner cursor-pointer">
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="flex items-center gap-2 mt-3 px-2 text-center text-xs font-medium text-zinc-400">
            @foreach($months as $month)
                <div class="flex-1">{{ $month }}</div>
            @endforeach
        </div>
    </div>
</div>