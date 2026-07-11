<?php
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Service;

new class extends Component {
    use WithPagination;

    public $sortBy = 'service_name';
    public $sortDirection = 'asc';

    public $service_name;
    public $service_price;
    public $description;

    public function sort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function services()
    {
        return Service::query()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(5); // Menggunakan 5 baris data agar pas & rapi dengan tata letak dasbor
    }

    #[Computed]
    public function stats()
    {
        return [
            'total' => Service::count(),
            'avg_price' => Service::avg('service_price') ?? 0,
            'total_value' => Service::sum('service_price'),
        ];
    }

    public function saveService()
    {
        $this->validate([
            'service_name' => 'required|string|max:255',
            'service_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        Service::create([
            'service_name' => $this->service_name,
            'service_price' => $this->service_price,
            'description' => $this->description,
        ]);

        $this->reset(['service_name', 'service_price', 'description']);

        session()->flash('success', 'Service created successfully');
        $this->redirectRoute('service.index', navigate: true);
    }

    public function edit($id){
        $this->dispatch('edit-service', id: $id);
    }
};?>

<div class="max-w-7xl mx-auto space-y-6 container pb-12 px-4">
    <x-flash-message />

    {{-- 1. BANNER UTAMA (HEADER ATAS) --}}
    <div class="relative bg-slate-900 rounded-2xl p-6 sm:p-8 overflow-hidden shadow-xl border border-slate-800">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-950/50 to-purple-950/30 mix-blend-multiply"></div>
        <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-2">
                    <span class="text-indigo-500">Service</span> Management
                </h1>
                <p class="text-slate-400 text-xs sm:text-sm mt-1">Kelola semua layanan bengkel dan atur tarif dengan mudah</p>
            </div>
            
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button wire:click="$refresh" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl border border-slate-700/60 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- 2. KONTEN UTAMA SPLIT KIRI & KANAN --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- PANEL KIRI: TAMBAH SERVICE FORM --}}
        <div class="lg:col-span-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-xs border border-zinc-200 dark:border-zinc-800/80">
            <div class="mb-5 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.83-5.83m0 0a2.943 2.943 0 0 1-4.097-4.098L16.35 2.1l-1.535 1.535a2.943 2.943 0 0 0 4.097 4.098l1.535-1.535L16.35 2.1m-5.17 5.17H2.25m9.344-2.25H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V16.5M16.5 13.25V18" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-black text-zinc-900 dark:text-white tracking-wide">Tambah Service</h2>
                    <p class="text-zinc-500 dark:text-zinc-400 text-[11px] mt-0.5">Buat layanan baru untuk bengkel</p>
                </div>
            </div>

            <form wire:submit.prevent="saveService" class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">Nama Service</label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.767c-.304.233-.443.626-.412 1.004.004.05.006.1.006.15s-.002.1-.006.15c-.03.378.11.771.412 1.004l1.003.767a1.125 1.125 0 0 1 .26 1.43l-1.296 2.247a1.125 1.125 0 0 1-1.37.49l-1.216-.456c-.356-.133-.751-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.767c.304-.233.442-.626.412-1.004a3.405 3.405 0 0 0-.006-.15c0-.05.002-.1.006-.15.03-.378-.11-.771-.412-1.004l-1.004-.767a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.49l1.216.456c.356.133.751.073 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <input type="text" wire:model="service_name" placeholder="Contoh: Service Karburator" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-transparent text-sm font-medium text-zinc-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">Harga (Rp)</label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-xs font-bold text-zinc-400 bg-zinc-50 dark:bg-zinc-800/60 px-3 rounded-l-xl border-r border-zinc-200 dark:border-zinc-700">
                            Rp
                        </div>
                        <input type="number" wire:model="service_price" placeholder="Masukkan harga service" class="w-full pl-14 pr-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-transparent text-sm font-bold tracking-wide text-zinc-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">Deskripsi / Kategori</label>
                    <textarea wire:model="description" rows="3" placeholder="Jelaskan detail service, estimasi pengerjaan, atau tanda khusus lainnya..." class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-transparent text-sm font-medium text-zinc-900 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                </div>

                <button type="submit" class="w-full font-bold tracking-wide bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3 px-4 shadow-sm flex items-center justify-center gap-2 transition-all active:scale-[0.98] mt-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Simpan Service
                </button>
            </form>
        </div>

        {{-- PANEL KANAN: DAFTAR SERVICE TABLE --}}
        <div class="lg:col-span-8 bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-xs border border-zinc-200 dark:border-zinc-800/80">
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-black text-zinc-900 dark:text-white tracking-wide">Daftar Service</h2>
                    <p class="text-zinc-500 dark:text-zinc-400 text-[11px] mt-0.5">Kelola semua layanan yang tersedia di bengkel</p>
                </div>
                
                {{-- Pencarian Sederhana --}}
                <div class="relative rounded-xl max-w-xs w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.603 10.602Z" />
                        </svg>
                    </div>
                    <input type="text" placeholder="Cari service..." class="w-full pl-9 pr-4 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-transparent text-xs font-medium text-zinc-700 dark:text-zinc-300 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20">
                </div>
            </div>

            <livewire:service.edit />

            {{-- ELEMEN TABEL PREMIUM --}}
            <div class="overflow-x-auto rounded-xl border border-zinc-100 dark:border-zinc-800/60 shadow-xs">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-100 dark:border-zinc-800">
                            <th class="px-4 py-3.5 text-xs font-black text-zinc-500 dark:text-zinc-400 w-12 text-center">No</th>
                            <th class="px-4 py-3.5 text-xs font-black text-zinc-500 dark:text-zinc-400 cursor-pointer hover:text-indigo-600 transition-colors" wire:click="sort('service_name')">
                                Nama Service 
                                @if($sortBy === 'service_name') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th class="px-4 py-3.5 text-xs font-black text-zinc-500 dark:text-zinc-400">Kategori</th>
                            <th class="px-4 py-3.5 text-xs font-black text-zinc-500 dark:text-zinc-400" wire:click="sort('service_price')">
                                Harga
                                @if($sortBy === 'service_price') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th class="px-4 py-3.5 text-xs font-black text-zinc-500 dark:text-zinc-400">Durasi</th>
                            <th class="px-4 py-3.5 text-xs font-black text-zinc-500 dark:text-zinc-400 text-center">Status</th>
                            <th class="px-4 py-3.5 text-xs font-black text-zinc-500 dark:text-zinc-400 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        @forelse ($this->services as $service)
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors duration-150">
                                <td class="px-4 py-4 text-xs font-bold text-zinc-400 text-center">
                                    {{ $this->services->firstItem() + $loop->index }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-400 border border-zinc-200/50 dark:border-zinc-700/50">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.83-5.83m0 0a2.943 2.943 0 0 1-4.097-4.098L16.35 2.1l-1.535 1.535a2.943 2.943 0 0 0 4.097 4.098l1.535-1.535L16.35 2.1m-5.17 5.17H2.25m9.344-2.25H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V16.5M16.5 13.25V18" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-bold text-zinc-900 dark:text-zinc-100 text-sm leading-tight">{{ $service->service_name }}</div>
                                            <div class="text-[11px] text-zinc-400 mt-0.5">Pemeriksaan komponen standar</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    @if($service->description)
                                        @if(str_contains(strtolower($service->description), 'vip'))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/40 uppercase tracking-wide">
                                                VIP
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-200/40">
                                                Mesin
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200/40">Umum</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 font-black text-zinc-900 dark:text-zinc-100 text-sm tracking-wide">
                                    Rp {{ number_format($service->service_price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-zinc-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        30 menit
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/40">
                                        Aktif
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button wire:click="edit({{ $service->id }})" class="p-1.5 rounded-lg bg-zinc-50 hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 border border-zinc-200/60 dark:border-zinc-700 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button wire:click="$dispatch('confirm-delete', {id: {{ $service->id }}})" class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-red-950/30 dark:hover:bg-red-950/50 text-red-600 dark:text-red-400 border border-red-200/40 dark:border-red-900/40 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-xs font-medium text-zinc-400 italic">
                                    Belum ada data service yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $this->services->links() }}
            </div>
        </div>
    </div>

    {{-- 3. BOTTOM PANEL: STATISTIK DINAMIS (BETAWI ACCENT BOX) --}}
    <div class="bg-slate-900 rounded-2xl p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 border border-slate-800 shadow-lg">
        
        <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-800 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Service</p>
                <p class="text-2xl font-black text-white mt-1">{{ $this->stats['total'] }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Semua layanan</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                </svg>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-800 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Service Aktif</p>
                <p class="text-2xl font-black text-emerald-400 mt-1">{{ $this->stats['total'] }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Layanan aktif</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-800 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata Harga</p>
                <p class="text-xl font-black text-amber-400 mt-1.5">Rp {{ number_format($this->stats['avg_price'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Rata-rata semua service</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m0-18h4a3 3 0 0 1 0 6h-4m0 0h4a3 3 0 0 1 0 6h-4m0 0v4m0-16h-4a3 3 0 0 0 0 6h4m0 0h-4a3 3 0 0 0 0 6h4" />
                </svg>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-800 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Nilai Paket</p>
                <p class="text-xl font-black text-purple-400 mt-1.5">Rp {{ number_format($this->stats['make_placeholder'] ?? 1980000, 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Dari layanan bulan ini</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797-2.101c.727.022 1.453.058 2.178.11 1.075.077 1.925.984 1.925 2.062 0 .604-.465 1.11-1.066 1.173-1.46.151-2.929.27-4.417.357a2.51 2.51 0 0 1-2.164-1.074l-2.977-4.69a2.503 2.503 0 0 1-.061-2.485L11.74 8.554a2.503 2.503 0 0 1 2.21-1.423h3.513c.426 0 .848.107 1.222.31L21 9m-9 9.75V10.5M3.75 4.5h.007m-.007 3h.007m-.007 3h.007m-.007 3h.007m-.007 3h.007M6 7.5h.008m-.008 3h.008m-.008 3h.008m-.008 3h.008m-4.5-12h.008M6 4.5h.008m.004 0h.008M6 18h.008m-.008-13.5h.008m0 0H12" />
                </svg>
            </div>
        </div>

    </div>
</div>