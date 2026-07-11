<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-Bengkel — Solusi Perawatan & Sparepart Motor</title>
    
    <!-- Fonts & Tailwind CSS via CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="antialiased bg-zinc-50 text-zinc-900 font-sans selection:bg-violet-500 selection:text-white">

    {{-- NAVBAR SECTION --}}
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-zinc-100">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-violet-600 rounded-xl flex items-center justify-center text-white text-xl shadow-md shadow-violet-500/20">
                    ⚙️
                </div>
                <div class="flex flex-col">
                    <span class="font-black tracking-tight text-zinc-900 text-lg leading-none">MotoBOT</span>
                    <span class="text-[10px] text-zinc-400 font-bold tracking-widest uppercase mt-0.5">Mikir Kit</span>
                </div>
            </div>

            <!-- Navigation Auth Links -->
            <nav class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-zinc-900 hover:bg-zinc-800 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-all shadow-sm">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-zinc-600 hover:text-zinc-900 font-bold text-sm px-4 py-2 transition-colors">
                            Gassss
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-violet-600 hover:bg-violet-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-all shadow-md shadow-violet-500/10">
                                Daftar
                            </a>
                        @endif
                    @endauth
                @endif
            </nav>
        </div>
    </header>

    {{-- HERO SECTION --}}
    <main class="relative overflow-hidden min-h-[calc(100vh-80px)] flex items-center border-b border-zinc-200/60">
        <!-- Background Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#e4e4e7_1px,transparent_1px),linear-gradient(to_bottom,#e4e4e7_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] opacity-40 z-0"></div>

        <!-- Background Image Full Screen -->
        <div class="absolute inset-0 w-full h-full opacity-50 pointer-events-none select-none z-0">
            <img src="{{ asset('images/bengkel.jpg') }}" class="w-full h-full object-cover" alt="Workshop Background">
            <div class="absolute inset-0 bg-gradient-to-r from-zinc-50 via-zinc-50/80 to-transparent"></div>
        </div>

        <div class="max-w-7xl mx-auto px-6 py-16 relative z-10 w-full">
            <div class="max-w-3xl space-y-6 text-left relative z-10">
                <div class="inline-flex items-center gap-2 bg-violet-50 border border-violet-200/60 px-3 py-1 rounded-full text-xs font-bold text-violet-700 shadow-sm shadow-violet-500/5">
                    <span>⚡ Sistem E-Commerce & Booking Bengkel Motor</span>
                </div>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-zinc-900 tracking-tight leading-[1.1]">
                    Rawat Motor Anda <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-indigo-600">Tanpa Perlu Mengantre.</span>
                </h1>
                
                <p class="text-base sm:text-lg text-zinc-500 max-w-2xl font-medium leading-relaxed">
                    Platform e-commerce sparepart motor original terlengkap dan sistem booking mekanik online. Atur jadwal servis berkala Anda secara cerdas, transparan, dan efisien langsung dari rumah.
                </p>

                <!-- PERUBAHAN: Tombol Pelajari Fitur dihapus, menyisakan Tombol Utama -->
                <div class="pt-4">
                    <a href="{{ route('login') }}" class="inline-block w-full sm:w-auto bg-violet-600 hover:bg-violet-700 text-white font-extrabold text-sm px-10 py-4 rounded-xl transition-all shadow-lg shadow-violet-500/20 text-center">
                        Mulai Servis Sekarang
                    </a>
                </div>

                <!-- Info Metrics -->
                <div class="grid grid-cols-3 gap-6 pt-10 border-t border-zinc-200/80 max-w-md">
                    <div>
                        <p class="text-2xl font-black text-zinc-900">100%</p>
                        <p class="text-xs font-semibold text-zinc-400 mt-1">Sparepart Ori</p>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-zinc-900">24/7</p>
                        <p class="text-xs font-semibold text-zinc-400 mt-1">Booking Sistem</p>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-zinc-900">A+</p>
                        <p class="text-xs font-semibold text-zinc-400 mt-1">Teknisi Ahli</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- FEATURES SECTION (KOTAK WARNA-WARNI ESTETIK) --}}
    <section id="features" class="py-24 bg-white relative z-10">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-3xl font-black tracking-tight text-zinc-900 sm:text-4xl">
                    7 Fitur Unggulan MotoBOT
                </h2>
                <p class="mt-4 text-zinc-500 font-medium text-sm sm:text-base">
                    Segala ekosistem perawatan motor dan pengelolaan sparepart kini terintegrasi penuh dalam satu platform modern.
                </p>
            </div>

            <!-- Grid Tampilan 7 Fitur Berwarna -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- 1. Category (Blue Theme) -->
                <div class="bg-blue-50/50 p-6 rounded-2xl border border-blue-100 hover:border-blue-300 shadow-sm flex flex-col gap-3 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-xl shadow-sm border border-blue-100 group-hover:scale-110 transition-transform">🗂️</div>
                    <div>
                        <h3 class="font-bold text-sm text-blue-950">Category Management</h3>
                        <p class="text-xs text-blue-700/90 leading-relaxed mt-1 font-medium">Klasifikasi sparepart dan jenis servis yang rapi berdasarkan tipe motor Anda (Matic, Bebek, atau Sport).</p>
                    </div>
                </div>

                <!-- 2. Spareparts (Emerald/Green Theme) -->
                <div class="bg-emerald-50/50 p-6 rounded-2xl border border-emerald-100 hover:border-emerald-300 shadow-sm flex flex-col gap-3 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-xl shadow-sm border border-emerald-100 group-hover:scale-110 transition-transform">⚙️</div>
                    <div>
                        <h3 class="font-bold text-sm text-emerald-950">Spareparts E-Commerce</h3>
                        <p class="text-xs text-emerald-700/90 leading-relaxed mt-1 font-medium">Katalog lengkap suku cadang, ban, komponen mesin, dan oli original bergaransi resmi toko.</p>
                    </div>
                </div>

                <!-- 3. Service (Amber/Yellow Theme) -->
                <div class="bg-amber-50/50 p-6 rounded-2xl border border-amber-100 hover:border-amber-300 shadow-sm flex flex-col gap-3 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-xl shadow-sm border border-amber-100 group-hover:scale-110 transition-transform">🔧</div>
                    <div>
                        <h3 class="font-bold text-sm text-amber-950">Service Options</h3>
                        <p class="text-xs text-amber-700/90 leading-relaxed mt-1 font-medium">Pilihan paket perawatan mulai dari servis rutin berkala, ganti oli cepat, hingga perbaikan mesin berat.</p>
                    </div>
                </div>

                <!-- 4. Orders -->
                <div class="bg-sky-50/50 p-6 rounded-2xl border border-sky-100 hover:border-sky-300 shadow-sm flex flex-col gap-3 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-xl shadow-sm border border-sky-100 group-hover:scale-110 transition-transform">🛒</div>
                    <div>
                        <h3 class="font-bold text-sm text-sky-950">Order Tracking</h3>
                        <p class="text-xs text-sky-700/90 leading-relaxed mt-1 font-medium">Pantau status nota pembelian sparepart Anda mulai dari tahap dikemas, dikirim, hingga sampai ke rumah.</p>
                    </div>
                </div>

                <!-- 5. Motorcycles (Rose/Red Theme) -->
                <div class="bg-rose-50/50 p-6 rounded-2xl border border-rose-100 hover:border-rose-300 shadow-sm flex flex-col gap-3 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-xl shadow-sm border border-rose-100 group-hover:scale-110 transition-transform">🛵</div>
                    <div>
                        <h3 class="font-bold text-sm text-rose-950">My Motorcycles (Garasi)</h3>
                        <p class="text-xs text-rose-700/90 leading-relaxed mt-1 font-medium">Simpan data spesifikasi motor-motor Anda untuk rekomendasi otomatis kecocokan produk suku cadang.</p>
                    </div>
                </div>

                <!-- 6. Bookings (Indigo Theme) -->
                <div class="bg-indigo-50/50 p-6 rounded-2xl border border-indigo-100 hover:border-indigo-300 shadow-sm flex flex-col gap-3 hover:shadow-md transition-all group">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-xl shadow-sm border border-indigo-100 group-hover:scale-110 transition-transform">📅</div>
                    <div>
                        <h3 class="font-bold text-sm text-indigo-950">Online Bookings</h3>
                        <p class="text-xs text-indigo-700/90 leading-relaxed mt-1 font-medium">Ambil nomor antrean servis digital dan pilih montir andalan Anda dari rumah agar tidak perlu mengantre.</p>
                    </div>
                </div>

                <!-- 7. Payments (Purple Theme - Center Pos) -->
                <div class="bg-purple-50/50 p-6 rounded-2xl border border-purple-100 hover:border-purple-300 shadow-sm flex flex-col gap-3 hover:shadow-md transition-all group md:col-span-2 lg:col-span-1 lg:col-start-2">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-xl shadow-sm border border-purple-100 group-hover:scale-110 transition-transform">💳</div>
                    <div>
                        <h3 class="font-bold text-sm text-purple-950">Secure Payments</h3>
                        <p class="text-xs text-purple-700/90 leading-relaxed mt-1 font-medium">Verifikasi pembayaran otomatis secara instan demi kenyamanan transaksi e-commerce maupun servis bengkel.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

</body>
</html>
