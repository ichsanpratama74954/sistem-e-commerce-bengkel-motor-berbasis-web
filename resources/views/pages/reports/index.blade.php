@component('layouts.app')

<div class="space-y-8 p-1">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                Laporan Penjualan
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Pantau performa penjualan dan transaksi bengkel motor Anda secara real-time.
            </p>
        </div>
        
        <a href="{{ route('reports.pdf', request()->query()) }}"
                class="inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md transition-all duration-200 group">
                    <svg class="w-5 h-5 text-white transition group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </a>
            </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Card Pendapatan -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md p-6 transition-all duration-200 flex items-center justify-between group">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Pendapatan</p>
                <h2 class="text-2xl font-bold text-emerald-600 tracking-tight">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </h2>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Card Orders -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md p-6 transition-all duration-200 flex items-center justify-between group">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Order</p>
                <h2 class="text-3xl font-extrabold text-slate-800">
                    {{ $totalOrders }}
                </h2>
            </div>
            <div class="p-3 bg-blue-50 rounded-xl text-blue-600 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
        </div>

        <!-- Card Booking -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md p-6 transition-all duration-200 flex items-center justify-between group">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Booking</p>
                <h2 class="text-3xl font-extrabold text-slate-800">
                    {{ $totalBookings }}
                </h2>
            </div>
            <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Card Customer -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md p-6 transition-all duration-200 flex items-center justify-between group">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Customer</p>
                <h2 class="text-3xl font-extrabold text-slate-800">
                    {{ $totalCustomers }}
                </h2>
            </div>
            <div class="p-3 bg-amber-50 rounded-xl text-amber-600 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

    </div>

    {{-- Grafik --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-800">
                Grafik Pendapatan Bulanan
            </h2>
            <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full font-medium">Jan - Des</span>
        </div>
        <div class="relative h-80 w-full">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Saring Berdasarkan Rentang Tanggal
        </h3>
        
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">
                    Dari Tanggal
                </label>
                <input
                    type="date"
                    name="start_date"
                    value="{{ request('start_date') }}"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">
                    Sampai Tanggal
                </label>
                <input
                    type="date"
                    name="end_date"
                    value="{{ request('end_date') }}"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>

            <div class="flex items-end">
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md shadow-blue-100 transition duration-150">
                    Terapkan Filter
                </button>
            </div>

            <div class="flex items-end">
                <a
                    href="{{ route('reports.index') }}"
                    class="w-full text-center bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold px-5 py-2.5 rounded-xl transition duration-150">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Transaksi --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-md font-bold text-slate-800">Rincian Transaksi Terbaru</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-max">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <th class="text-left py-3.5 px-6">No</th>
                        <th class="text-left py-3.5">ID Order</th>
                        <th class="text-left py-3.5">Customer</th>
                        <th class="text-left py-3.5">Tanggal</th>
                        <th class="text-left py-3.5">Total Belanja</th>
                        <th class="text-left py-3.5 px-6">Status</th>
                    </tr>
                </thead>
                <tbody class="text-slate-600 text-sm divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/50 transition duration-150">
                            <td class="py-4 px-6 font-medium text-slate-400">{{ $loop->iteration }}</td>
                            <td class="py-4 font-bold text-blue-600">#{{ $order->id }}</td>
                            <td class="py-4 font-medium text-slate-700">{{ $order->user->name ?? 'Guest' }}</td>
                            <td class="py-4 text-slate-500">{{ $order->created_at->format('d M Y') }}</td>
                            <td class="py-4 font-bold text-slate-800">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold 
                                    {{ $order->status == 'completed' || $order->status == 'paid' 
                                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' 
                                        : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $order->status == 'completed' || $order->status == 'paid' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="font-medium text-sm">Belum ada data transaksi yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<!-- Load Chart.js secara aman -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function loadSalesChart() {
    if (typeof Chart === 'undefined') {
        console.log('Chart.js belum dimuat');
        return;
    }

    const canvas = document.getElementById('salesChart');
    if (!canvas) return;

    // Bersihkan chart lama jika ada agar tidak tumpang tindih saat navigasi
    if (window.salesChart instanceof Chart) {
        window.salesChart.destroy();
    }

    const chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const chartTotals = Array(12).fill(0);

    @foreach($monthlySales as $item)
        chartTotals[{{ $item->month - 1 }}] = {{ $item->total }};
    @endforeach

    window.salesChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: chartTotals,
                borderWidth: 0,
                borderRadius: 8,
                backgroundColor: 'rgba(59, 130, 246, 0.85)', // Biru Tailwind
                borderColor: 'rgb(37, 99, 235)',
                hoverBackgroundColor: 'rgb(29, 78, 216)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9'
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 }
                    }
                }
            }
        }
    });
}

// Jalankan saat halaman pertama kali dibuka maupun saat navigasi Livewire dipicu
document.addEventListener('DOMContentLoaded', loadSalesChart);
document.addEventListener('livewire:navigated', loadSalesChart);
</script>
@endpush

@endcomponent