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

    // Menyimpan ID service yang sedang diedit
    public $editingServiceId = null; 

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
            ->paginate(5);
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

    // Mengisi form dengan data yang ingin diedit
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        $this->editingServiceId = $service->id;
        $this->service_name = $service->service_name;
        $this->service_price = $service->service_price;
        $this->description = $service->description;
    }

    // Membatalkan mode edit dan mereset form
    public function cancelEdit()
    {
        $this->reset(['service_name', 'service_price', 'description', 'editingServiceId']);
    }

    // Menyimpan data (bisa Create baru atau Update data lama)
    public function saveService()
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Anda tidak memiliki izin untuk melakukan ini.');
            return;
        }

        $this->validate([
            'service_name' => 'required|string|max:255',
            'service_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        if ($this->editingServiceId) {
            // Mode Update
            $service = Service::findOrFail($this->editingServiceId);
            $service->update([
                'service_name' => $this->service_name,
                'service_price' => $this->service_price,
                'description' => $this->description,
            ]);
            session()->flash('success', 'Service updated successfully');
        } else {
            // Mode Tambah Baru
            Service::create([
                'service_name' => $this->service_name,
                'service_price' => $this->service_price,
                'description' => $this->description,
            ]);
            session()->flash('success', 'Service created successfully');
        }

        $this->reset(['service_name', 'service_price', 'description', 'editingServiceId']);
        $this->redirectRoute('service.index', navigate: true);
    }

    // Fungsi Hapus Service
    public function delete($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Anda tidak memiliki izin untuk melakukan ini.');
            return;
        }

        $service = Service::findOrFail($id);
        $service->delete();

        session()->flash('success', 'Service deleted successfully');
        $this->redirectRoute('service.index', navigate: true);
    }
};?>

<div class="max-w-7xl mx-auto space-y-8 container pb-16 px-4 sm:px-6">
    <x-flash-message />

    {{-- BANNER UTAMA --}}
    <div class="relative rounded-3xl p-8 overflow-hidden shadow-2xl border border-slate-800" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-purple-500/20 rounded-full blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Dashboard Panel
                </span>
                <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Service Management</h1>
                <p class="text-slate-300 text-sm max-w-xl leading-relaxed">Kelola semua daftar layanan, harga, dan deskripsi bengkel Anda dalam satu antarmuka yang responsif.</p>
            </div>
            
            <div class="hidden md:flex items-center justify-center bg-white/5 border border-white/10 rounded-2xl p-4 self-center shadow-inner">
                <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- GRID LAYOUT --}}
    @if(auth()->user()->role === 'admin')
        {{-- LAYOUT ADMIN --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- Form Tambah / Edit --}}
            <div class="lg:col-span-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-lg border border-zinc-200/80 dark:border-zinc-800/80 transition-all duration-300">
                <div class="flex items-center gap-2 mb-6 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <div class="p-2 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-zinc-950 dark:text-white">
                        {{ $editingServiceId ? 'Edit Service' : 'Tambah Service' }}
                    </h2>
                </div>

                <form wire:submit.prevent="saveService" class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider mb-2">Nama Service</label>
                        <input type="text" wire:model="service_name" placeholder="Contoh: Ganti Oli Mesin" class="w-full px-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-transparent text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200">
                        @error('service_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider mb-2">Harga (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-zinc-400 dark:text-zinc-500">Rp</span>
                            <input type="number" wire:model="service_price" placeholder="0" class="w-full pl-10 pr-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-transparent text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200">
                        </div>
                        @error('service_price') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider mb-2">Deskripsi Layanan</label>
                        <textarea wire:model="description" rows="3" placeholder="Jelaskan detail tindakan layanan..." class="w-full px-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-transparent text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 resize-none"></textarea>
                        @error('description') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-2">
                        @if($editingServiceId)
                            <button type="button" wire:click="cancelEdit" class="w-1/3 font-bold bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 rounded-xl py-3.5 px-4 transition-all duration-200 active:scale-[0.98]">
                                Batal
                            </button>
                        @endif
                        <button type="submit" class="{{ $editingServiceId ? 'w-2/3' : 'w-full' }} font-bold bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl py-3.5 px-4 shadow-md shadow-indigo-500/10 transition-all duration-200 active:scale-[0.98]">
                            {{ $editingServiceId ? 'Update' : 'Simpan Service' }}
                        </button>
                    </div>
                </form>
            </div>
            
            {{-- Tabel Data Admin --}}
            <div class="lg:col-span-8 bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-lg border border-zinc-200/80 dark:border-zinc-800/80">
                <div class="flex items-center justify-between mb-6 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h2 class="text-lg font-bold text-zinc-950 dark:text-white">Daftar Layanan Aktif</h2>
                </div>
                
                <div class="overflow-x-auto rounded-xl border border-zinc-100 dark:border-zinc-800/60">
                    <table class="w-full text-sm text-left text-zinc-500 dark:text-zinc-400">
                        <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-800/40 dark:text-zinc-300 border-b border-zinc-100 dark:border-zinc-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 cursor-pointer select-none group" wire:click="sort('service_name')">
                                    <div class="flex items-center gap-1.5">
                                        Nama Service
                                        <span class="text-zinc-400 group-hover:text-indigo-500 transition-colors">
                                            @if($sortBy === 'service_name')
                                                {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                                            @else
                                                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                            @endif
                                        </span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 cursor-pointer select-none group" wire:click="sort('service_price')">
                                    <div class="flex items-center gap-1.5">
                                        Harga
                                        <span class="text-zinc-400 group-hover:text-indigo-500 transition-colors">
                                            @if($sortBy === 'service_price')
                                                {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                                            @else
                                                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                            @endif
                                        </span>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4">Deskripsi</th>
                                <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                            @forelse($this->services as $service)
                                <tr class="bg-white dark:bg-zinc-900 hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors duration-150">
                                    <td class="px-6 py-4 font-semibold text-zinc-950 dark:text-white">
                                        {{ $service->service_name }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-indigo-600 dark:text-indigo-400">
                                        Rp {{ number_format($service->service_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400 max-w-[200px] truncate" title="{{ $service->description }}">
                                        {{ $service->description ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button wire:click="edit({{ $service->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-lg transition-all duration-150">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                Edit
                                            </button>
                                            <button wire:click="delete({{ $service->id }})" wire:confirm="Apakah Anda yakin ingin menghapus layanan ini?" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-950 text-red-600 dark:text-red-400 rounded-lg transition-all duration-150">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-zinc-300 dark:text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v2m4.586-1H12m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span class="font-medium text-sm">Tidak ada data service.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="p-4 border-t border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/20">
                        {{ $this->services->links() }}
                    </div>
                </div>

            </div>
        </div>
    @else
        {{-- LAYOUT USER BIASA (Tanpa Form Edit/Hapus) --}}
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-lg border border-zinc-200/80 dark:border-zinc-800/80">
            <h2 class="text-lg font-bold text-zinc-950 dark:text-white mb-6">Daftar Layanan Bengkel</h2>
            
            <div class="overflow-x-auto rounded-xl border border-zinc-100 dark:border-zinc-800/60">
                <table class="w-full text-sm text-left text-zinc-500 dark:text-zinc-400">
                    <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-800/40 dark:text-zinc-300 border-b border-zinc-100 dark:border-zinc-800">
                        <tr>
                            <th scope="col" class="px-6 py-4 cursor-pointer select-none group" wire:click="sort('service_name')">
                                <div class="flex items-center gap-1.5">
                                    Nama Service
                                    <span class="text-zinc-400 group-hover:text-indigo-500 transition-colors">
                                        @if($sortBy === 'service_name')
                                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                                        @else
                                            <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                        @endif
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 cursor-pointer select-none group" wire:click="sort('service_price')">
                                <div class="flex items-center gap-1.5">
                                    Harga
                                    <span class="text-zinc-400 group-hover:text-indigo-500 transition-colors">
                                        @if($sortBy === 'service_price')
                                            {!! $sortDirection === 'asc' ? '▲' : '▼' !!}
                                        @else
                                            <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                        @endif
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        @forelse($this->services as $service)
                            <tr class="bg-white dark:bg-zinc-900 hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 transition-colors duration-150">
                                <td class="px-6 py-4 font-semibold text-zinc-950 dark:text-white">
                                    {{ $service->service_name }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-indigo-600 dark:text-indigo-400">
                                    Rp {{ number_format($service->service_price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-zinc-500 dark:text-zinc-400">
                                    {{ $service->description ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-8 h-8 text-zinc-300 dark:text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v2m4.586-1H12m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="font-medium text-sm">Tidak ada data service.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4 border-t border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/20">
                    {{ $this->services->links() }}
                </div>
            </div>

        </div>
    @endif

    {{-- KARTU STATISTIK --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        {{-- Total Service --}}
        <div class="relative overflow-hidden bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-md hover:border-indigo-500/20 transition-all duration-300">
            <div class="absolute right-4 top-4 text-indigo-500/10">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Total Service</p>
                <p class="text-3xl font-black text-white mt-2">{{ $this->stats['total'] }}</p>
                <div class="mt-2 w-12 h-1 bg-indigo-500 rounded-full"></div>
            </div>
        </div>

        {{-- Rata-Rata Harga --}}
        <div class="relative overflow-hidden bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-md hover:border-amber-500/20 transition-all duration-300">
            <div class="absolute right-4 top-4 text-amber-500/10">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Rata-Rata Harga</p>
                <p class="text-2xl font-black text-amber-400 mt-2">Rp {{ number_format($this->stats['avg_price'], 0, ',', '.') }}</p>
                <div class="mt-2.5 w-12 h-1 bg-amber-500 rounded-full"></div>
            </div>
        </div>

        {{-- Total Nilai Paket --}}
        <div class="relative overflow-hidden bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-md hover:border-purple-500/20 transition-all duration-300">
            <div class="absolute right-4 top-4 text-purple-500/10">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Total Nilai Paket</p>
                <p class="text-2xl font-black text-purple-400 mt-2">Rp {{ number_format($this->stats['total_value'], 0, ',', '.') }}</p>
                <div class="mt-2.5 w-12 h-1 bg-purple-500 rounded-full"></div>
            </div>
        </div>
    </div>
</div>