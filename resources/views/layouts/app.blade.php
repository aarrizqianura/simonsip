<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - SIMONSIP RSUD Dr. H. Moch. Ansari Saleh</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS (CDN Play) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        ansari: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            dark: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(203, 213, 225, 0.5);
            border-radius: 4px;
        }
        @media print {
            .no-print { display: none !important; }
            .print-full { width: 100% !important; margin: 0 !important; padding: 0 !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex" x-data="{ sidebarOpen: false }">

    <!-- Sidebar Backdrop for Mobile -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         x-cloak 
         class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden transition-opacity">
    </div>

    <!-- Sidebar Component -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed lg:static inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-slate-900 via-slate-900 to-ansari-900 text-white flex flex-col transition-transform duration-300 ease-in-out shadow-2xl">
        
        <!-- Hospital Branding Header -->
        <div class="p-6 border-b border-slate-800/80 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 font-bold text-xl ring-2 ring-emerald-400/30">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold tracking-wider text-emerald-400 uppercase">RSUD ANSARI SALEH</div>
                    <div class="text-lg font-extrabold tracking-tight text-white flex items-center gap-1.5">
                        SIMONSIP
                        <span class="text-[10px] bg-emerald-500/20 text-emerald-300 font-medium px-2 py-0.5 rounded-full border border-emerald-500/30">v1.0</span>
                    </div>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- System Role Indicator -->
        <div class="px-6 py-3 bg-slate-800/40 border-b border-slate-800/60 flex items-center justify-between text-xs">
            <span class="text-slate-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Mode Akses:
            </span>
            <span class="font-semibold text-emerald-300 bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-700/50">
                {{ auth()->user()->role_label }}
            </span>
        </div>

        <!-- Navigation Menu -->
        <div class="flex-1 overflow-y-auto sidebar-scroll px-4 py-5 space-y-6">
            
            <!-- Group 1: Monitoring Utama -->
            <div>
                <div class="px-3 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Monitoring & Layanan</div>
                <nav class="space-y-1">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-chart-pie w-5 text-center text-emerald-400"></i>
                            <span>Dashboard Monitoring</span>
                        </div>
                    </a>

                    @php
                        $pendingCount = \App\Models\SipRecord::where('status', 'menunggu_verifikasi')->count();
                    @endphp
                    <a href="{{ route('verifikasi.index') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('verifikasi.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-file-circle-check w-5 text-center text-sky-400"></i>
                            <span>Verifikasi SIP Baru</span>
                        </div>
                        @if($pendingCount > 0)
                            <span class="bg-amber-500 text-slate-900 text-xs font-black px-2 py-0.5 rounded-full animate-bounce shadow">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('whatsapp.index') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('whatsapp.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-brands fa-whatsapp w-5 text-center text-green-400 text-base"></i>
                            <span>Matriks & Blast WA</span>
                        </div>
                        <span class="text-[10px] bg-slate-800 text-slate-300 px-2 py-0.5 rounded-full border border-slate-700">Cron 07.00</span>
                    </a>
                </nav>
            </div>

            <!-- Group 2: Master Data Kepegawaian -->
            <div>
                <div class="px-3 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Data Kepegawaian</div>
                <nav class="space-y-1">
                    <a href="{{ route('dokters.index') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dokters.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-user-doctor w-5 text-center text-teal-400"></i>
                            <span>Master Data Dokter</span>
                        </div>
                    </a>

                    <a href="{{ route('ksm.index') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('ksm.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-sitemap w-5 text-center text-indigo-400"></i>
                            <span>Master Data KSM</span>
                        </div>
                    </a>

                    <a href="{{ route('spesialisasi.index') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('spesialisasi.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-stethoscope w-5 text-center text-cyan-400"></i>
                            <span>Master Spesialisasi</span>
                        </div>
                    </a>
                </nav>
            </div>

            <!-- Group 3: Pelaporan & Eksekutif -->
            <div>
                <div class="px-3 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Audit & Akreditasi</div>
                <nav class="space-y-1">
                    <a href="{{ route('laporan.index') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('laporan.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-file-invoice w-5 text-center text-amber-400"></i>
                            <span>Laporan & Rekapitulasi</span>
                        </div>
                        <span class="text-[10px] bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded">PDF/XLS</span>
                    </a>
                </nav>
            </div>

            <!-- Group 4: Super Admin Khusus -->
            @if(auth()->user()->isSuperAdmin())
            <div>
                <div class="px-3 mb-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Konfigurasi Sistem</div>
                <nav class="space-y-1">
                    <a href="{{ route('users.index') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-users-gear w-5 text-center text-rose-400"></i>
                            <span>Manajemen Pengguna</span>
                        </div>
                    </a>
                </nav>
            </div>
            @endif

        </div>

        <!-- Sidebar Footer / User Profile -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-900/80">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 truncate">
                    <div class="w-9 h-9 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center font-bold text-sm shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <div class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-400 truncate">{{ auth()->user()->username }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar dari Sistem" class="text-slate-400 hover:text-rose-400 p-2 rounded-lg hover:bg-slate-800 transition-colors">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen overflow-x-hidden">
        
        <!-- Top Navbar -->
        <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 px-4 lg:px-8 py-3.5 flex items-center justify-between shadow-sm">
            
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-slate-600 hover:text-slate-900 p-1.5 rounded-lg border border-slate-200">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div>
                    <h1 class="text-lg lg:text-xl font-bold text-slate-900 leading-tight">@yield('page_title', 'Dashboard Monitoring')</h1>
                    <div class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                        <i class="fa-regular fa-clock text-emerald-600"></i>
                        <span>Zona WITA Banjarmasin: {{ \Carbon\Carbon::now()->timezone('Asia/Makassar')->translatedFormat('l, d F Y - H:i') }} WITA</span>
                    </div>
                </div>
            </div>

            <!-- Topbar Quick Actions -->
            <div class="flex items-center gap-3">
                
                <!-- Quick Trigger Early Warning -->
                <form action="{{ route('whatsapp.trigger') }}" method="POST" onsubmit="return confirm('Jalankan cron job pemeriksaan masa berlaku SIP dan kirim WhatsApp sekarang?');">
                    @csrf
                    <button type="submit" 
                            title="Jalankan Pengecekan SIP & WhatsApp Blast" 
                            class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition shadow-sm">
                        <i class="fa-solid fa-bolt text-emerald-600"></i>
                        <span>Trigger Early Warning</span>
                    </button>
                </form>

                <!-- Shortcut Input Dokter Baru -->
                <a href="{{ route('dokters.create') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-md shadow-emerald-600/20 hover:from-emerald-700 hover:to-teal-700 transition">
                    <i class="fa-solid fa-user-plus"></i>
                    <span class="hidden md:inline">Tambah Dokter Baru</span>
                </a>

            </div>

        </header>

        <!-- Flash Messages Alert -->
        <main class="flex-1 p-4 lg:p-8 max-w-7xl w-full mx-auto">
            
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-cloak class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start justify-between shadow-sm">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5"></i>
                    <div>
                        <div class="font-bold text-sm">Operasi Berhasil</div>
                        <div class="text-sm mt-0.5">{{ session('success') }}</div>
                    </div>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-800"><i class="fa-solid fa-xmark"></i></button>
            </div>
            @endif

            @if(session('warning'))
            <div x-data="{ show: true }" x-show="show" x-cloak class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-start justify-between shadow-sm">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg mt-0.5"></i>
                    <div>
                        <div class="font-bold text-sm">Perhatian</div>
                        <div class="text-sm mt-0.5">{{ session('warning') }}</div>
                    </div>
                </div>
                <button @click="show = false" class="text-amber-500 hover:text-amber-800"><i class="fa-solid fa-xmark"></i></button>
            </div>
            @endif

            @if(session('error') || $errors->any())
            <div x-data="{ show: true }" x-show="show" x-cloak class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start justify-between shadow-sm">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-xmark text-rose-600 text-lg mt-0.5"></i>
                    <div>
                        <div class="font-bold text-sm">Terjadi Kesalahan</div>
                        @if(session('error'))
                            <div class="text-sm mt-0.5">{{ session('error') }}</div>
                        @endif
                        @if($errors->any())
                            <ul class="list-disc list-inside text-xs mt-1 space-y-0.5 text-rose-700">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                <button @click="show = false" class="text-rose-500 hover:text-rose-800"><i class="fa-solid fa-xmark"></i></button>
            </div>
            @endif

            @yield('content')

        </main>

        <!-- Hospital System Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 px-6 text-center text-xs text-slate-500 mt-auto">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>
                    <strong>SIMONSIP</strong> &copy; {{ date('Y') }} RSUD Dr. H. Moch. Ansari Saleh Banjarmasin. Subbag SDM & SIRS IT.
                </div>
                <div class="flex items-center gap-3 text-slate-400">
                    <span>Standar STARKES / KARS</span>
                    <span>&bull;</span>
                    <span>Early Warning Gateway</span>
                </div>
            </div>
        </footer>

    </div>

    @stack('scripts')
</body>
</html>
