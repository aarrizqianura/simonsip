<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMONSIP RSUD Dr. H. Moch. Ansari Saleh Banjarmasin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        ansari: {
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-900 font-sans antialiased min-h-screen flex items-center justify-center p-4 sm:p-6 relative overflow-x-hidden overflow-y-auto" 
      x-data="{ 
        username: '{{ old('username', 'adminsdm') }}', 
        password: '{{ old('username') ? '' : 'Ansari@2026' }}',
        showPass: false,
        fillAccount(u, p) {
            this.username = u;
            this.password = p;
        }
      }">

    <!-- Decorative Gradient Glow Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500/20 rounded-full blur-3xl"></div>
    </div>

    <!-- Main Container Card: Mobile (flex-col) & Desktop (lg:flex-row) -->
    <div class="w-full max-w-5xl bg-white/95 backdrop-blur-xl rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-700/30 flex flex-col lg:flex-row overflow-hidden my-auto relative z-10">
        
        <!-- Left Column: Hospital Info & Branding Hero -->
        <div class="w-full lg:w-5/12 bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950 p-6 sm:p-8 lg:p-10 text-white flex flex-col justify-between relative">
            <div class="space-y-5 lg:space-y-6">
                <!-- Hospital Logo Badge -->
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-xl shadow-emerald-500/30 font-bold text-xl sm:text-2xl ring-4 ring-emerald-500/20 shrink-0">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                    <div>
                        <div class="text-[10px] sm:text-[11px] font-bold text-emerald-400 tracking-wider uppercase">Pemerintah Provinsi Kalsel</div>
                        <div class="text-base sm:text-lg font-black tracking-tight text-white leading-tight">
                            RSUD Dr. H. Moch. Ansari Saleh
                        </div>
                    </div>
                </div>

                <div class="pt-2 sm:pt-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mb-3">
                        <i class="fa-solid fa-shield-halved text-emerald-400"></i> Early Warning Legalitas Medis
                    </span>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight leading-snug">
                        Sistem Monitoring SIP Dokter <span class="text-emerald-400">(SIMONSIP)</span>
                    </h1>
                    <p class="text-slate-300 text-xs sm:text-sm mt-2 sm:mt-3 leading-relaxed">
                        Platform integratif pemantauan Surat Izin Praktik (SIP) dokter, otomatisasi notifikasi WhatsApp Gateway, dan pelaporan kepatuhan standar akreditasi rumah sakit (STARKES / KARS).
                    </p>
                </div>

                <!-- Feature Highlights -->
                <div class="space-y-2.5 pt-1 sm:pt-2 text-xs">
                    <div class="flex items-center gap-2.5 text-slate-300">
                        <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                            <i class="fa-brands fa-whatsapp text-xs"></i>
                        </div>
                        <span class="text-[11px] sm:text-xs">WhatsApp Gateway Reminder (90, 60, & 30 Hari)</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-slate-300">
                        <div class="w-6 h-6 rounded-full bg-teal-500/20 text-teal-400 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-id-card-clip text-xs"></i>
                        </div>
                        <span class="text-[11px] sm:text-xs">Penanganan Multi-SIP (Praktik 1, 2, 3) & STR Seumur Hidup</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-slate-300">
                        <div class="w-6 h-6 rounded-full bg-sky-500/20 text-sky-400 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-circle-check text-xs"></i>
                        </div>
                        <span class="text-[11px] sm:text-xs">1-Step Renewal Mandiri & SDM Approval Queue</span>
                    </div>
                </div>
            </div>

            <!-- Footer Hospital Info -->
            <div class="pt-6 sm:pt-8 mt-4 sm:mt-6 text-[11px] sm:text-xs text-slate-400 border-t border-slate-800/80">
                Jl. Brigjend H. Hasan Basry No. 1, Alalak Utara, Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan 70125
            </div>
        </div>

        <!-- Right Column: Login Form -->
        <div class="w-full lg:w-7/12 p-6 sm:p-8 lg:p-10 flex flex-col justify-center bg-white">
            <div class="max-w-md w-full mx-auto space-y-5 sm:space-y-6">
                
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Masuk ke Portal</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Silakan masukkan NIP / Username dan kata sandi Anda</p>
                </div>

                <!-- Quick Role Switcher for Demo / Testing -->
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <div class="text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span>Pilih Akun Demo (1-Klik Isi):</span>
                        <i class="fa-solid fa-wand-magic-sparkles text-emerald-600"></i>
                    </div>
                    <div class="grid grid-cols-3 gap-1.5 text-[11px] sm:text-xs">
                        <button type="button" @click="fillAccount('adminsdm', 'Ansari@2026')" class="py-1.5 px-2 rounded-lg bg-emerald-100/70 hover:bg-emerald-200 text-emerald-800 font-bold transition text-center truncate border border-emerald-300/50">
                            Admin SDM
                        </button>
                        <button type="button" @click="fillAccount('superadmin', 'Ansari@2026')" class="py-1.5 px-2 rounded-lg bg-indigo-100/70 hover:bg-indigo-200 text-indigo-800 font-bold transition text-center truncate border border-indigo-300/50">
                            Super Admin
                        </button>
                        <button type="button" @click="fillAccount('198503152010011002', 'Ansari@2026')" class="py-1.5 px-2 rounded-lg bg-teal-100/70 hover:bg-teal-200 text-teal-800 font-bold transition text-center truncate border border-teal-300/50">
                            dr. Fauzi (Sp.A)
                        </button>
                    </div>
                </div>

                @if($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                @if(session('success'))
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Username / NIP Dokter
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <input type="text" 
                                   name="username" 
                                   x-model="username"
                                   required 
                                   placeholder="Contoh: adminsdm atau 19850315..." 
                                   class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-sm text-slate-900 transition font-medium">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Kata Sandi
                            </label>
                            <span class="text-[11px] sm:text-xs text-slate-400">Default: Ansari@YYYY</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input :type="showPass ? 'text' : 'password'" 
                                   name="password" 
                                   x-model="password"
                                   required 
                                   placeholder="••••••••" 
                                   class="w-full pl-10 pr-12 py-2.5 sm:py-3 rounded-xl border border-slate-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-sm text-slate-900 transition font-medium">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                                <i :class="showPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span>Ingat Sesi Saya</span>
                        </label>
                        <span class="text-xs text-slate-400">Hubungi SDM jika lupa sandi</span>
                    </div>

                    <button type="submit" 
                            class="w-full py-3 sm:py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition transform active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                        <span>Masuk ke SIMONSIP</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="pt-4 border-t border-slate-100 text-center text-[11px] sm:text-xs text-slate-400">
                    Sistem Monitoring SIP Dokter &bull; RSUD Dr. H. Moch. Ansari Saleh Banjarmasin
                </div>

            </div>
        </div>

    </div>

</body>
</html>
