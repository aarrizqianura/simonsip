<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\Ksm;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use App\Models\Spesialisasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $today = Carbon::today();

        // 1. Super Admin Account
        $superAdmin = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Administrator (IT/SIRS)',
                'email' => 'it.support@ansarisaleh.kalselprov.go.id',
                'password' => Hash::make('Ansari@2026'),
                'role' => 'super_admin',
            ]
        );

        // 2. Admin SDM Account
        $adminSdm = User::updateOrCreate(
            ['username' => 'adminsdm'],
            [
                'name' => 'Hj. Noor Laila, S.Kep., Ns (Subbag SDM)',
                'email' => 'kepegawaian@ansarisaleh.kalselprov.go.id',
                'password' => Hash::make('Ansari@2026'),
                'role' => 'admin_sdm',
            ]
        );

        // 3. KSM (Kelompok Staf Medis)
        $ksmData = [
            'KSM Bedah',
            'KSM Anak',
            'KSM Penyakit Dalam',
            'KSM Obstetri & Ginekologi (Obgyn)',
            'KSM Jantung & Pembuluh Darah',
            'KSM Neurologi / Saraf',
            'KSM Mata',
            'KSM THT-KL',
            'KSM Anestesiologi & Terapi Intensif',
            'KSM Radiologi',
            'KSM Patologi Klinik',
            'KSM Paru',
            'KSM Kedokteran Jiwa',
        ];

        $ksmMap = [];
        foreach ($ksmData as $nama) {
            $ksm = Ksm::firstOrCreate(['nama_ksm' => $nama]);
            $ksmMap[$nama] = $ksm->id;
        }

        // 4. Spesialisasi
        $spesialisasiData = [
            'Dokter Spesialis Bedah Umum (Sp.B)',
            'Dokter Spesialis Anak (Sp.A)',
            'Dokter Spesialis Penyakit Dalam (Sp.PD)',
            'Dokter Spesialis Obstetri & Ginekologi (Sp.OG)',
            'Dokter Spesialis Jantung & Pembuluh Darah (Sp.JP)',
            'Dokter Spesialis Neurologi (Sp.N)',
            'Dokter Spesialis Mata (Sp.M)',
            'Dokter Spesialis THT-KL (Sp.THT-KL)',
            'Dokter Spesialis Anestesiologi (Sp.An)',
            'Dokter Spesialis Radiologi (Sp.Rad)',
            'Dokter Spesialis Patologi Klinik (Sp.PK)',
            'Dokter Spesialis Paru (Sp.P)',
            'Dokter Spesialis Kedokteran Jiwa (Sp.KJ)',
        ];

        $spesMap = [];
        foreach ($spesialisasiData as $nama) {
            $spes = Spesialisasi::firstOrCreate(['nama_spesialisasi' => $nama]);
            $spesMap[$nama] = $spes->id;
        }

        // Dummy sample SIP document PDF file in public storage
        Storage::disk('public')->makeDirectory('sip_documents');
        $dummyPdfPath = 'sip_documents/sample_sip_ansari.pdf';
        if (!Storage::disk('public')->exists($dummyPdfPath)) {
            // Write simple text or dummy PDF representation
            Storage::disk('public')->put($dummyPdfPath, "%PDF-1.4\n%SIMONSIP Sample Surat Izin Praktik RSUD Dr. H. Moch. Ansari Saleh\n%%EOF");
        }

        // 5. Daftar Dokter Profil dengan status beragam
        $dokterSeeds = [
            // [1] STATUS 🟢 AMAN (> 90 hari)
            [
                'name' => 'dr. H. Ahmad Fauzi, Sp.A',
                'nip' => '198503152010011002',
                'wa' => '081251234567',
                'ksm' => 'KSM Anak',
                'spes' => 'Dokter Spesialis Anak (Sp.A)',
                'str' => '3312100320145678',
                'str_seumur_hidup' => true,
                'str_exp' => null,
                'sip' => [
                    [
                        'no_sip' => '503/0142/SIP-D/DPMPTSP/2024',
                        'praktik_ke' => 1,
                        'terbit' => $today->copy()->subMonths(10)->format('Y-m-d'),
                        'exp' => $today->copy()->addDays(297)->format('Y-m-d'), // 297 hari (Aman)
                        'status' => 'aktif',
                        'is_verified' => true,
                    ]
                ]
            ],
            // [2] STATUS 🟡 PERINGATAN (61-90 hari)
            [
                'name' => 'dr. Hj. Siti Rahmah, Sp.PD',
                'nip' => '198807202014022001',
                'wa' => '081349887766',
                'ksm' => 'KSM Penyakit Dalam',
                'spes' => 'Dokter Spesialis Penyakit Dalam (Sp.PD)',
                'str' => '3311200420167890',
                'str_seumur_hidup' => true,
                'str_exp' => null,
                'sip' => [
                    [
                        'no_sip' => '503/0288/SIP-D/DPMPTSP/2021',
                        'praktik_ke' => 1,
                        'terbit' => $today->copy()->subYears(4)->subMonths(10)->format('Y-m-d'),
                        'exp' => $today->copy()->addDays(75)->format('Y-m-d'), // 75 hari (Peringatan)
                        'status' => 'warning',
                        'is_verified' => true,
                    ]
                ]
            ],
            // [3] STATUS 🟠 MENDESAK (31-60 hari) & Multi-SIP
            [
                'name' => 'dr. Muhammad Rizky, Sp.B',
                'nip' => '198205102008031004',
                'wa' => '082155443322',
                'ksm' => 'KSM Bedah',
                'spes' => 'Dokter Spesialis Bedah Umum (Sp.B)',
                'str' => '3311100220123456',
                'str_seumur_hidup' => true,
                'str_exp' => null,
                'sip' => [
                    [
                        'no_sip' => '503/0311/SIP-D/DPMPTSP/2021',
                        'praktik_ke' => 1,
                        'terbit' => $today->copy()->subYears(4)->subMonths(11)->format('Y-m-d'),
                        'exp' => $today->copy()->addDays(49)->format('Y-m-d'), // 49 hari (Mendesak)
                        'status' => 'mendesak',
                        'is_verified' => true,
                    ],
                    [
                        'no_sip' => '503/0512/SIP-D/DPMPTSP-KOTA/2023',
                        'praktik_ke' => 2,
                        'terbit' => $today->copy()->subMonths(18)->format('Y-m-d'),
                        'exp' => $today->copy()->addDays(420)->format('Y-m-d'),
                        'status' => 'aktif',
                        'is_verified' => true,
                    ]
                ]
            ],
            // [4] STATUS 🔴 KADALUARSA (<= 30 hari)
            [
                'name' => 'dr. Endang Sri Wahyuni, Sp.OG',
                'nip' => '197911142006042003',
                'wa' => '085248112233',
                'ksm' => 'KSM Obstetri & Ginekologi (Obgyn)',
                'spes' => 'Dokter Spesialis Obstetri & Ginekologi (Sp.OG)',
                'str' => '3321100120108901',
                'str_seumur_hidup' => false,
                'str_exp' => $today->copy()->addMonths(6)->format('Y-m-d'), // STR Masih lama
                'sip' => [
                    [
                        'no_sip' => '503/0095/SIP-D/DPMPTSP/2021',
                        'praktik_ke' => 1,
                        'terbit' => $today->copy()->subYears(5)->format('Y-m-d'),
                        'exp' => $today->copy()->addDays(14)->format('Y-m-d'), // 14 hari lagi (Expired/Kritis)
                        'status' => 'expired',
                        'is_verified' => true,
                    ]
                ]
            ],
            // [5] STATUS 🔴 EXPIRED (Lewat tanggal)
            [
                'name' => 'dr. Hendra Saputra, Sp.M',
                'nip' => '197604182005011003',
                'wa' => '081350998811',
                'ksm' => 'KSM Mata',
                'spes' => 'Dokter Spesialis Mata (Sp.M)',
                'str' => '3311300220112345',
                'str_seumur_hidup' => true,
                'str_exp' => null,
                'sip' => [
                    [
                        'no_sip' => '503/0187/SIP-D/DPMPTSP/2020',
                        'praktik_ke' => 1,
                        'terbit' => $today->copy()->subYears(5)->subMonths(1)->format('Y-m-d'),
                        'exp' => $today->copy()->subDays(15)->format('Y-m-d'), // Telah kadaluarsa 15 hari lalu
                        'status' => 'expired',
                        'is_verified' => true,
                    ]
                ]
            ],
            // [6] STATUS 🔵 MENUNGGU VERIFIKASI (Telah Mengajukan Renewal)
            [
                'name' => 'dr. Bambang Prasetyo, Sp.JP, FIHA',
                'nip' => '198402122009011005',
                'wa' => '081299887711',
                'ksm' => 'KSM Jantung & Pembuluh Darah',
                'spes' => 'Dokter Spesialis Jantung & Pembuluh Darah (Sp.JP)',
                'str' => '3314100520156789',
                'str_seumur_hidup' => true,
                'str_exp' => null,
                'sip' => [
                    [
                        'no_sip' => '503/0991/SIP-D/DPMPTSP/2026',
                        'praktik_ke' => 1,
                        'terbit' => $today->copy()->subDays(3)->format('Y-m-d'),
                        'exp' => $today->copy()->addYears(5)->format('Y-m-d'),
                        'status' => 'menunggu_verifikasi',
                        'is_verified' => false,
                    ]
                ]
            ],
            // [7] STATUS 🟢 AMAN - Multi-SIP 3 Praktik
            [
                'name' => 'dr. Nurul Hidayati, Sp.N',
                'nip' => '198609082012022002',
                'wa' => '085344556677',
                'ksm' => 'KSM Neurologi / Saraf',
                'spes' => 'Dokter Spesialis Neurologi (Sp.N)',
                'str' => '3312200320144321',
                'str_seumur_hidup' => true,
                'str_exp' => null,
                'sip' => [
                    [
                        'no_sip' => '503/0411/SIP-D/DPMPTSP/2024',
                        'praktik_ke' => 1,
                        'terbit' => $today->copy()->subMonths(6)->format('Y-m-d'),
                        'exp' => $today->copy()->addDays(580)->format('Y-m-d'),
                        'status' => 'aktif',
                        'is_verified' => true,
                    ],
                    [
                        'no_sip' => '503/0122/SIP-D/DPMPTSP/2024',
                        'praktik_ke' => 2,
                        'terbit' => $today->copy()->subMonths(4)->format('Y-m-d'),
                        'exp' => $today->copy()->addDays(620)->format('Y-m-d'),
                        'status' => 'aktif',
                        'is_verified' => true,
                    ],
                    [
                        'no_sip' => '503/0789/SIP-D/DPMPTSP-BJM/2025',
                        'praktik_ke' => 3,
                        'terbit' => $today->copy()->subMonths(2)->format('Y-m-d'),
                        'exp' => $today->copy()->addDays(700)->format('Y-m-d'),
                        'status' => 'aktif',
                        'is_verified' => true,
                    ]
                ]
            ],
            // [8] STATUS 🟢 AMAN - Anestesi
            [
                'name' => 'dr. Reza Wardhana, Sp.An, KIC',
                'nip' => '198711052014031001',
                'wa' => '081277665544',
                'ksm' => 'KSM Anestesiologi & Terapi Intensif',
                'spes' => 'Dokter Spesialis Anestesiologi (Sp.An)',
                'str' => '3311100520169876',
                'str_seumur_hidup' => true,
                'str_exp' => null,
                'sip' => [
                    [
                        'no_sip' => '503/0654/SIP-D/DPMPTSP/2025',
                        'praktik_ke' => 1,
                        'terbit' => $today->copy()->subMonths(3)->format('Y-m-d'),
                        'exp' => $today->copy()->addDays(810)->format('Y-m-d'),
                        'status' => 'aktif',
                        'is_verified' => true,
                    ]
                ]
            ]
        ];

        foreach ($dokterSeeds as $d) {
            // Auto generate account user
            $user = User::updateOrCreate(
                ['username' => $d['nip']],
                [
                    'name' => $d['name'],
                    'email' => $d['nip'] . '@dokter.ansarisaleh.local',
                    'password' => Hash::make('Ansari@2026'),
                    'role' => 'dokter',
                ]
            );

            $dokter = Dokter::updateOrCreate(
                ['nip_nik' => $d['nip']],
                [
                    'user_id' => $user->id,
                    'nama_lengkap' => $d['name'],
                    'no_whatsapp' => $d['wa'],
                    'ksm_id' => $ksmMap[$d['ksm']],
                    'spesialisasi_id' => $spesMap[$d['spes']],
                    'no_str' => $d['str'],
                    'is_str_seumur_hidup' => $d['str_seumur_hidup'],
                    'tgl_kadaluarsa_str' => $d['str_exp'],
                ]
            );

            foreach ($d['sip'] as $sipData) {
                $sip = SipRecord::updateOrCreate(
                    [
                        'dokter_id' => $dokter->id,
                        'no_sip' => $sipData['no_sip'],
                        'tempat_praktik_ke' => $sipData['praktik_ke'],
                    ],
                    [
                        'tgl_terbit' => $sipData['terbit'],
                        'tgl_kadaluarsa' => $sipData['exp'],
                        'file_sip_path' => $dummyPdfPath,
                        'status' => $sipData['status'],
                        'is_verified' => $sipData['is_verified'],
                        'verified_by' => $sipData['is_verified'] ? $adminSdm->id : null,
                        'verified_at' => $sipData['is_verified'] ? Carbon::now()->subDays(5) : null,
                    ]
                );

                // Add sample notification logs
                if ($sipData['status'] === 'warning') {
                    NotificationLog::firstOrCreate([
                        'sip_record_id' => $sip->id,
                        'recipient_phone' => $dokter->no_whatsapp,
                        'message_type' => 'early_warning',
                    ], [
                        'channel' => 'whatsapp',
                        'payload' => "🟡 Peringatan SIP Tahap 1: SIP No. {$sip->no_sip} tersisa 75 hari.",
                        'status' => 'sent',
                        'sent_at' => Carbon::now()->subDays(2),
                    ]);
                } elseif ($sipData['status'] === 'mendesak') {
                    NotificationLog::firstOrCreate([
                        'sip_record_id' => $sip->id,
                        'recipient_phone' => $dokter->no_whatsapp,
                        'message_type' => 'urgent',
                    ], [
                        'channel' => 'whatsapp',
                        'payload' => "🟠 Peringatan SIP Tahap 2: SIP No. {$sip->no_sip} tersisa 49 hari.",
                        'status' => 'sent',
                        'sent_at' => Carbon::now()->subDay(),
                    ]);
                } elseif ($sipData['status'] === 'expired') {
                    NotificationLog::firstOrCreate([
                        'sip_record_id' => $sip->id,
                        'recipient_phone' => $dokter->no_whatsapp,
                        'message_type' => 'urgent',
                    ], [
                        'channel' => 'whatsapp',
                        'payload' => "🔴 PERINGATAN KRITIS: SIP No. {$sip->no_sip} mendekati/telah kadaluarsa.",
                        'status' => 'sent',
                        'sent_at' => Carbon::now()->subHours(4),
                    ]);
                }
            }
        }
    }
}
