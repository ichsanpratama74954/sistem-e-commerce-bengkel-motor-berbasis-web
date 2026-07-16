<?php

use App\Livewire\Forms\PaymentForm;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Booking;
use App\Models\Order;

new class extends Component {
    public PaymentForm $form;
    public $bookings = [];
    public $orders = [];
    
    // State tambahan untuk kontrol UI baru
    public $is_locked = false;
    public $source_details = '';
    public $cash_received = '';
    public $change = 0;

    public function mount()
    {
        $this->form = new PaymentForm($this, 'form');
        $this->bookings = Booking::select('id', 'booking_date', 'status')->with('user:id,name')->get();
        $this->orders = Order::select('id', 'total_amount', 'status')->with('user:id,name')->get();
    }

    #[On('pay-booking')]
    public function payBooking($id)
    {
        $booking = Booking::with(['user', 'bookingDetails'])->find($id);
        if ($booking) {
            $this->form->paymentable_type = 'App\Models\Booking';
            $this->form->paymentable_id = $booking->id;
            $this->form->amount = $booking->bookingDetails->sum('subtotal');
            $this->form->payment_status = 'Success'; // Status transaksi pembayaran lunas
            
            // Kunci UI & buat info detail
            $this->is_locked = true;
            $this->source_details = "Booking #" . $booking->id . " - " . ($booking->user?->name ?? 'N/A') . " (" . \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') . ")";
            
            // Reset kalkulator kembalian
            $this->cash_received = '';
            $this->change = 0;

            Flux::modal('create-payment')->show();
        }
    }

    #[On('pay-order')]
    public function payOrder($id)
    {
        $order = Order::with('user')->find($id);
        if ($order) {
            $this->form->paymentable_type = 'App\Models\Order';
            $this->form->paymentable_id = $order->id;
            $this->form->amount = $order->total_amount;
            $this->form->payment_status = 'Success'; // Status transaksi pembayaran lunas
            
            // Kunci UI & buat info detail
            $this->is_locked = true;
            $this->source_details = "Order #" . $order->id . " - " . ($order->user?->name ?? 'N/A') . " (Rp " . number_format($order->total_amount, 0, ',', '.') . ")";
            
            // Reset kalkulator kembalian
            $this->cash_received = '';
            $this->change = 0;

            Flux::modal('create-payment')->show();
        }
    }

    // Reaksi otomatis ketika input form atau uang kasir berubah
    public function updated($property, $value)
    {
        if ($property === 'form.payment_method') {
            if ($value !== 'Cash') {
                $this->cash_received = $this->form->amount;
                $this->change = 0;
            } else {
                $this->cash_received = '';
                $this->change = 0;
            }
        }

        if ($property === 'cash_received') {
            $amount = floatval($this->form->amount ?: 0);
            $received = floatval($value ?: 0);
            $this->change = $received >= $amount ? ($received - $amount) : 0;
        }
    }

    public function save()
    {
        // Validasi tambahan jika menggunakan metode Cash tapi uangnya kurang
        if ($this->form->payment_method === 'Cash' && floatval($this->cash_received ?: 0) < floatval($this->form->amount)) {
            $this->addError('cash_received', 'Uang yang diterima kurang dari total tagihan.');
            return;
        }

        // 🌟 AMANKAN VALUE SEBELUM DI-RESET OLEH $this->form->store()
        $paymentableType = $this->form->paymentable_type;
        $paymentableId = $this->form->paymentable_id;

        // 1. Simpan pembayaran ke database lewat Form Object bawaanmu
        $this->form->store();

        // 2. UPDATE OTOMATIS STATUS BOOKING / ORDER MENJADI BERHASIL
        if ($paymentableType === 'App\Models\Booking') {
            $booking = Booking::find($paymentableId);
            if ($booking) {
                // Diubah menggunakan properti langsung + save() untuk bypass proteksi $fillable
                // Ubah 'approved' di bawah ini jika status lunas di websitemu menggunakan kata lain (misal: 'Success' atau 'Completed')
                $booking->status = 'approved'; 
                $booking->save();
            }
        } elseif ($paymentableType === 'App\Models\Order') {
            $order = Order::find($paymentableId);
            if ($order) {
                $order->status = 'Success'; 
                $order->save();
            }
        }

        // 3. Tutup modal dan arahkan kembali menggunakan URL langsung
        Flux::modal('create-payment')->close();
        session()->flash('success', 'Payment created successfully');
        $this->redirect('/bookings', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
        $this->is_locked = false;
        $this->source_details = '';
        $this->cash_received = '';
        $this->change = 0;
    }
};?>

<div>
    <flux:modal name="create-payment" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-6" wire:submit.prevent="save">
            
            <!-- Header Modal -->
            <div class="space-y-1">
                <flux:heading size="lg">Form Transaksi Pembayaran</flux:heading>
                <flux:text>Pastikan data transaksi kasir telah sesuai sebelum memproses transaksi ini.</flux:text>
            </div>

            <!-- Bagian Form Utama -->
            <div class="space-y-5">
                
                <!-- 📌 Jika Dipicu dari Tabel (Tampilan Banner Info Rapi & Premium) -->
                @if($is_locked)
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-1">
                        <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider block">Sumber Pembayaran</span>
                        <div class="flex justify-between items-center">
                            <strong class="text-zinc-800 dark:text-zinc-200 text-sm">
                                {{ $form->paymentable_type === 'App\Models\Booking' ? '📅 Booking (Servis Bengkel)' : '📦 Order (Sparepart)' }}
                            </strong>
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-400 text-sm font-medium">
                            {{ $source_details }}
                        </p>
                    </div>
                @else
                    <!-- 📌 Jika Dibuka Manual/Kosongan (Sistem Dropdown Asli Tetap Bekerja) -->
                    <div class="grid grid-cols-2 gap-4">
                        <flux:select label="Source Type" wire:model.live="form.paymentable_type">
                            <flux:select.option value="">Select Type</flux:select.option>
                            <flux:select.option value="App\Models\Booking">Booking (Service)</flux:select.option>
                            <flux:select.option value="App\Models\Order">Order (Sparepart)</flux:select.option>
                        </flux:select>

                        <flux:select label="Source ID" wire:model="form.paymentable_id">
                            <flux:select.option value="">Select Source</flux:select.option>
                            @if($form->paymentable_type === 'App\Models\Booking')
                                @foreach($bookings as $b)
                                    <flux:select.option value="{{ $b->id }}">Booking #{{ $b->id }} - {{ $b->user?->name ?? 'N/A' }} ({{ $b->booking_date }})</flux:select.option>
                                @endforeach
                            @elseif($form->paymentable_type === 'App\Models\Order')
                                @foreach($orders as $o)
                                    <flux:select.option value="{{ $o->id }}">Order #{{ $o->id }} - {{ $o->user?->name ?? 'N/A' }} (Rp {{ number_format($o->total_amount, 0, ',', '.') }})</flux:select.option>
                                @endforeach
                            @endif
                        </flux:select>
                    </div>
                @endif

                <!-- Input Nominal Total Tagihan (Kunci Jika Data Spesifik) -->
                <flux:field>
                    <flux:label>Total Tagihan (Rp)</flux:label>
                    <flux:input 
                        type="number" 
                        placeholder="Enter total amount" 
                        wire:model="form.amount" 
                        :disabled="$is_locked"
                        class="font-mono text-zinc-700 dark:text-zinc-200 font-bold {{ $is_locked ? 'bg-zinc-100 dark:bg-zinc-800' : '' }}"
                    />
                </flux:field>
                
                <!-- Metode Pembayaran & Status -->
                <div class="grid grid-cols-2 gap-4">
                    <flux:select label="Payment Method" wire:model.live="form.payment_method" placeholder="Select method...">
                        <flux:select.option value="Cash">Cash / Tunai</flux:select.option>
                        <flux:select.option value="Transfer">Bank Transfer</flux:select.option>
                        <flux:select.option value="QRIS">QRIS / E-Wallet</flux:select.option>
                        <flux:select.option value="DANA">DANA</flux:select.option>
                    </flux:select>

                    <flux:select label="Payment Status" wire:model="form.payment_status" placeholder="Select status...">
                        <flux:select.option value="Pending">Pending</flux:select.option>
                        <flux:select.option value="Success">Success</flux:select.option>
                        <flux:select.option value="Failed">Failed</flux:select.option>
                    </flux:select>
                </div>

                <!-- 🧮 Fitur Kalkulator Kasir Dinamis (Hanya muncul jika memilih Cash) -->
                @if($form->payment_method === 'Cash')
                    <div class="grid grid-cols-2 gap-4 p-4 bg-emerald-50/50 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/20 rounded-xl">
                        <flux:field>
                            <flux:label class="text-emerald-800 dark:text-emerald-400 font-semibold">Tunai Diterima</flux:label>
                            <flux:input 
                                type="number" 
                                wire:model.live="cash_received" 
                                placeholder="Uang tunai..."
                                class="font-mono text-base"
                            />
                            <flux:error name="cash_received" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="text-emerald-800 dark:text-emerald-400 font-semibold">Uang Kembalian</flux:label>
                            <flux:input 
                                type="text" 
                                readonly 
                                class="bg-zinc-150 dark:bg-zinc-800 font-mono font-bold text-emerald-600 dark:text-emerald-400 text-base"
                                value="Rp {{ number_format($change, 0, ',', '.') }}"
                            />
                        </flux:field>
                    </div>
                @endif
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="primary" type="submit">Selesaikan Pembayaran</flux:button>
            </div>
        </form>
    </flux:modal>
</div>