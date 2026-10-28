<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Dokter') - SIMONSIP RSUD Dr. H. Moch. Ansari Saleh</title>
    
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

    <style>
        [x-cloak] { display: none !important; }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100/80 text-slate-800 font-sans antialiased min-h-screen flex flex-col" x-data="{ mobileMenuOpen: false }">

    <!-- Doctor Portal Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white shadow-md shadow-emerald-600/30 font-bold text-lg">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-emerald-700 tracking-wider uppercase">PORTAL DOKTER</div>
                        <div class="text-base font-extrabold text-slate-900 tracking-tight leading-tight">
                            SIMONSIP Ansari Saleh
                        </div>
                    </div>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dokter.dashboard') }}" 
                       class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('dokter.dashboard') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-house mr-1.5 text-emerald-600"></i> Dashboard Saya
                    </a>

                    <a href="{{ route('dokter.renewal') }}" 
                       class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('dokter.renewal*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Unggah Perpanjangan SIP
                    </a>

                    <a href="{{ route('dokter.riwayat') }}" 
                       class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('dokter.riwayat*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-clock-rotate-left mr-1.5 text-teal-600"></i> Riwayat SIP
                    </a>
                </nav>

                <!-- User Profile & Logout -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('dokter.profile') }}" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 transition text-xs font-semibold text-slate-700">
                        <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] font-bold">
                            dr
                        </div>
                        <span class="max-w-[140px] truncate">{{ auth()->user()->name }}</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar" class="text-slate-500 hover:text-rose-600 p-2 rounded-lg hover:bg-slate-100 transition">
                            <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                        </button>
                    </form>

                    <!-- Mobile Menu Toggle -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-slate-600 p-2 rounded-lg border border-slate-200">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-2">
            <a href="{{ route('dokter.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                <i class="fa-solid fa-house mr-2 text-emerald-600"></i> Dashboard Saya
            </a>
            <a href="{{ route('dokter.renewal') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                <i class="fa-solid fa-cloud-arrow-up mr-2 text-teal-600"></i> Unggah Perpanjangan SIP
            </a>
            <a href="{{ route('dokter.riwayat') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                <i class="fa-solid fa-clock-rotate-left mr-2 text-indigo-600"></i> Riwayat SIP
            </a>
            <a href="{{ route('dokter.profile') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                <i class="fa-solid fa-user-gear mr-2 text-slate-600"></i> Profil & Ganti Password
            </a>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
        
        @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5"></i>
            <div>
                <div class="font-bold text-sm">Berhasil</div>
                <div class="text-sm mt-0.5">{{ session('success') }}</div>
            </div>
        </div>
        @endif

        @if(session('warning'))
        <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-start gap-3 shadow-sm">
            <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg mt-0.5"></i>
            <div>
                <div class="font-bold text-sm">Peringatan Legalitas</div>
                <div class="text-sm mt-0.5">{{ session('warning') }}</div>
            </div>
        </div>
        @endif

        @if(session('error') || $errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-sm">
            <i class="fa-solid fa-circle-xmark text-rose-600 text-lg mt-0.5"></i>
            <div>
                <div class="font-bold text-sm">Informasi</div>
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
        @endif

        @yield('content')
    </main>

    <!-- Doctor Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 px-6 text-center text-xs text-slate-500 mt-auto">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                <strong>SIMONSIP Portal Staf Medis</strong> &copy; {{ date('Y') }} RSUD Dr. H. Moch. Ansari Saleh Banjarmasin
            </div>
            <div class="text-slate-400">
                Pusat Bantuan SDM Kepegawaian: (0511) 3300759 / ext. 104
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
