<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\Ksm;
use App\Models\SipRecord;
use App\Models\Spesialisasi;
use App\Models\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DokterController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $ksmId = $request->query('ksm_id');
        $status = $request->query('status');

        $query = Dokter::with(['ksm', 'spesialisasi', 'sipRecords', 'user']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nip_nik', 'like', "%{$search}%")
                    ->orWhere('no_str', 'like', "%{$search}%")
                    ->orWhereHas('sipRecords', fn($sq) => $sq->where('no_sip', 'like', "%{$search}%"));
            });
        }

        if ($ksmId) {
            $query->where('ksm_id', $ksmId);
        }

        if ($status) {
            $query->whereHas('sipRecords', fn($sq) => $sq->where('status', $status));
        }

        $dokters = $query->orderBy('nama_lengkap')->paginate(12)->withQueryString();
        $ksms = Ksm::orderBy('nama_ksm')->get();

        return view('dokters.index', compact('dokters', 'ksms', 'search', 'ksmId', 'status'));
    }

    public function create(): View
    {
        $ksms = Ksm::orderBy('nama_ksm')->get();
        $spesialisasis = Spesialisasi::orderBy('nama_spesialisasi')->get();
        $defaultPassword = 'Ansari@' . date('Y');

        return view('dokters.create', compact('ksms', 'spesialisasis', 'defaultPassword'));
    }

    public function store(Request $request, WhatsAppService $waService): RedirectResponse
    {
        $validated = $request->validate([
            // Section 1: User Credentials
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],

            // Section 2: Biodata
            'nip_nik' => ['required', 'string', 'max:50', 'unique:dokters,nip_nik'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'no_whatsapp' => ['required', 'string', 'max:20'],
            'ksm_id' => ['required', 'exists:ksm,id'],
            'spesialisasi_id' => ['required', 'exists:spesialisasi,id'],
            'no_str' => ['required', 'string', 'max:100'],
            'is_str_seumur_hidup' => ['nullable', 'boolean'],
            'tgl_kadaluarsa_str' => ['nullable', 'date', 'required_if:is_str_seumur_hidup,0'],

            // Section 3: Legalitas SIP Awal
            'no_sip' => ['required', 'string', 'max:100'],
            'tempat_praktik_ke' => ['required', 'integer', 'between:1,3'],
            'tgl_terbit' => ['required', 'date'],
            'tgl_kadaluarsa' => ['required', 'date', 'after:tgl_terbit'],
            'file_sip' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],

            'send_wa' => ['nullable', 'boolean'],
        ], [
            'username.unique' => 'Username/NIP tersebut sudah digunakan akun lain.',
            'nip_nik.unique' => 'NIP/NIK dokter sudah terdaftar di sistem.',
            'tgl_kadaluarsa.after' => 'Tanggal kadaluarsa SIP harus setelah tanggal terbit.',
            'file_sip.max' => 'Ukuran berkas SIP maksimal 10 MB.',
        ]);

        DB::beginTransaction();
        try {
            // 1. Create User Account
            $user = User::create([
                'name' => $validated['nama_lengkap'],
                'username' => $validated['username'],
                'email' => $validated['username'] . '@dokter.ansarisaleh.local',
                'password' => Hash::make($validated['password']),
                'role' => 'dokter',
            ]);

            // 2. Create Dokter Profile
            $isStrSeumurHidup = $request->boolean('is_str_seumur_hidup', true);
            $dokter = Dokter::create([
                'user_id' => $user->id,
                'nip_nik' => $validated['nip_nik'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'no_whatsapp' => $validated['no_whatsapp'],
                'ksm_id' => $validated['ksm_id'],
                'spesialisasi_id' => $validated['spesialisasi_id'],
                'no_str' => $validated['no_str'],
                'is_str_seumur_hidup' => $isStrSeumurHidup,
                'tgl_kadaluarsa_str' => $isStrSeumurHidup ? null : $validated['tgl_kadaluarsa_str'],
            ]);

            // 3. Handle File Upload
            $filePath = 'sip_documents/sample_sip_ansari.pdf';
            if ($request->hasFile('file_sip')) {
                $file = $request->file('file_sip');
                $filename = 'sip_' . $dokter->nip_nik . '_p' . $validated['tempat_praktik_ke'] . '_' . time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('sip_documents', $filename, 'public');
            }

            // 4. Create Initial SIP Record
            $sip = new SipRecord([
                'dokter_id' => $dokter->id,
                'no_sip' => $validated['no_sip'],
                'tempat_praktik_ke' => $validated['tempat_praktik_ke'],
                'tgl_terbit' => $validated['tgl_terbit'],
                'tgl_kadaluarsa' => $validated['tgl_kadaluarsa'],
                'file_sip_path' => $filePath,
                'is_verified' => true,
                'verified_by' => auth()->id(),
                'verified_at' => Carbon::now(),
            ]);
            $sip->status = $sip->determineStatus();
            $sip->save();

            DB::commit();

            // 5. Send WA Credentials Notification if checked
            if ($request->boolean('send_wa')) {
                $waService->sendAccountCreatedNotification($dokter, $user, $validated['password'], $sip);
            }

            return redirect()->route('dokters.show', $dokter)
                ->with('success', "Data dokter {$dokter->nama_lengkap} dan akun login berhasil didaftarkan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function show(Dokter $dokter): View
    {
        $dokter->load(['ksm', 'spesialisasi', 'sipRecords.notificationLogs', 'user']);
        return view('dokters.show', compact('dokter'));
    }

    public function edit(Dokter $dokter): View
    {
        $ksms = Ksm::orderBy('nama_ksm')->get();
        $spesialisasis = Spesialisasi::orderBy('nama_spesialisasi')->get();
        $dokter->load(['ksm', 'spesialisasi', 'sipRecords']);

        return view('dokters.edit', compact('dokter', 'ksms', 'spesialisasis'));
    }

    public function update(Request $request, Dokter $dokter): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'no_whatsapp' => ['required', 'string', 'max:20'],
            'ksm_id' => ['required', 'exists:ksm,id'],
            'spesialisasi_id' => ['required', 'exists:spesialisasi,id'],
            'no_str' => ['required', 'string', 'max:100'],
            'is_str_seumur_hidup' => ['nullable', 'boolean'],
            'tgl_kadaluarsa_str' => ['nullable', 'date', 'required_if:is_str_seumur_hidup,0'],
            'password_baru' => ['nullable', 'string', 'min:6'],
        ]);

        $isStrSeumurHidup = $request->boolean('is_str_seumur_hidup', true);

        $dokter->update([
            'nama_lengkap' => $validated['nama_lengkap'],
            'no_whatsapp' => $validated['no_whatsapp'],
            'ksm_id' => $validated['ksm_id'],
            'spesialisasi_id' => $validated['spesialisasi_id'],
            'no_str' => $validated['no_str'],
            'is_str_seumur_hidup' => $isStrSeumurHidup,
            'tgl_kadaluarsa_str' => $isStrSeumurHidup ? null : $validated['tgl_kadaluarsa_str'],
        ]);

        // Update user account name and optionally password
        if ($dokter->user) {
            $userUpdate = ['name' => $validated['nama_lengkap']];
            if (!empty($validated['password_baru'])) {
                $userUpdate['password'] = Hash::make($validated['password_baru']);
            }
            $dokter->user->update($userUpdate);
        }

        return redirect()->route('dokters.show', $dokter)->with('success', 'Perubahan data profil dokter berhasil disimpan.');
    }

    public function destroy(Dokter $dokter): RedirectResponse
    {
        $nama = $dokter->nama_lengkap;
        if ($dokter->user) {
            $dokter->user->delete(); // cascades dokter and sip_records
        } else {
            $dokter->delete();
        }

        return redirect()->route('dokters.index')->with('success', "Data dokter {$nama} berhasil dihapus.");
    }

    /**
     * Add SIP record to existing doctor (for Multi-SIP Praktik 2 or 3)
     */
    public function storeSip(Request $request, Dokter $dokter): RedirectResponse
    {
        $validated = $request->validate([
            'no_sip' => ['required', 'string', 'max:100'],
            'tempat_praktik_ke' => ['required', 'integer', 'between:1,3'],
            'tgl_terbit' => ['required', 'date'],
            'tgl_kadaluarsa' => ['required', 'date', 'after:tgl_terbit'],
            'file_sip' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $filePath = 'sip_documents/sample_sip_ansari.pdf';
        if ($request->hasFile('file_sip')) {
            $file = $request->file('file_sip');
            $filename = 'sip_' . $dokter->nip_nik . '_p' . $validated['tempat_praktik_ke'] . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('sip_documents', $filename, 'public');
        }

        $sip = new SipRecord([
            'dokter_id' => $dokter->id,
            'no_sip' => $validated['no_sip'],
            'tempat_praktik_ke' => $validated['tempat_praktik_ke'],
            'tgl_terbit' => $validated['tgl_terbit'],
            'tgl_kadaluarsa' => $validated['tgl_kadaluarsa'],
            'file_sip_path' => $filePath,
            'is_verified' => true,
            'verified_by' => auth()->id(),
            'verified_at' => Carbon::now(),
        ]);
        $sip->status = $sip->determineStatus();
        $sip->save();

        return redirect()->route('dokters.show', $dokter)->with('success', "SIP Praktik Ke-{$sip->tempat_praktik_ke} berhasil ditambahkan.");
    }

    /**
     * Export Doctors list to CSV
     */
    public function exportCsv(): StreamedResponse
    {
        $fileName = 'data_dokter_ansari_saleh_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'NIP / NIK',
                'Nama Lengkap',
                'KSM',
                'Spesialisasi',
                'No. WhatsApp',
                'No. STR',
                'Status STR',
                'Praktik Ke-1 SIP',
                'Tgl Kadaluarsa SIP 1',
                'Sisa Hari',
                'Status Kepatuhan',
            ], ';');

            $dokters = Dokter::with(['ksm', 'spesialisasi', 'sipRecords'])->get();
            $no = 1;

            foreach ($dokters as $d) {
                $sip1 = $d->sipRecords->where('tempat_praktik_ke', 1)->first() ?? $d->sipRecords->first();
                fputcsv($handle, [
                    $no++,
                    $d->nip_nik,
                    $d->nama_lengkap,
                    $d->ksm?->nama_ksm ?? '-',
                    $d->spesialisasi?->nama_spesialisasi ?? '-',
                    $d->no_whatsapp,
                    $d->no_str,
                    $d->is_str_seumur_hidup ? 'Seumur Hidup' : ($d->tgl_kadaluarsa_str?->format('d/m/Y') ?? '-'),
                    $sip1?->no_sip ?? '-',
                    $sip1?->tgl_kadaluarsa?->format('d/m/Y') ?? '-',
                    $sip1 ? $sip1->sisa_hari . ' Hari' : '-',
                    $sip1?->status_label ?? 'Belum Ada SIP',
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Import Excel/CSV view and processing
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file_import' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('file_import');
        $path = $file->getRealPath();

        $rows = array_map(function($line) {
            return str_getcsv($line, ';');
        }, file($path));

        if (count($rows) <= 1) {
            return back()->with('error', 'File CSV kosong atau format tidak sesuai.');
        }

        // Header: NIP; Nama; KSM; Spesialisasi; No WA; No STR; No SIP; Tgl Kadaluarsa (YYYY-MM-DD)
        $imported = 0;
        $header = array_shift($rows);

        foreach ($rows as $row) {
            if (count($row) < 7 || empty(trim($row[0]))) continue;

            $nip = trim($row[0]);
            $nama = trim($row[1]);
            $ksmName = trim($row[2]);
            $spesName = trim($row[3]);
            $wa = trim($row[4]);
            $str = trim($row[5]);
            $sipNo = trim($row[6]);
            $expDate = isset($row[7]) && !empty(trim($row[7])) ? trim($row[7]) : Carbon::now()->addYears(2)->format('Y-m-d');

            $ksm = Ksm::firstOrCreate(['nama_ksm' => $ksmName ?: 'KSM Umum']);
            $spes = Spesialisasi::firstOrCreate(['nama_spesialisasi' => $spesName ?: 'Dokter Umum']);

            $user = User::firstOrCreate(
                ['username' => $nip],
                [
                    'name' => $nama,
                    'email' => $nip . '@dokter.ansarisaleh.local',
                    'password' => Hash::make('Ansari@' . date('Y')),
                    'role' => 'dokter',
                ]
            );

            $dokter = Dokter::updateOrCreate(
                ['nip_nik' => $nip],
                [
                    'user_id' => $user->id,
                    'nama_lengkap' => $nama,
                    'no_whatsapp' => $wa,
                    'ksm_id' => $ksm->id,
                    'spesialisasi_id' => $spes->id,
                    'no_str' => $str,
                    'is_str_seumur_hidup' => true,
                ]
            );

            $sip = SipRecord::updateOrCreate(
                ['dokter_id' => $dokter->id, 'tempat_praktik_ke' => 1],
                [
                    'no_sip' => $sipNo,
                    'tgl_terbit' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                    'tgl_kadaluarsa' => $expDate,
                    'file_sip_path' => 'sip_documents/sample_sip_ansari.pdf',
                    'is_verified' => true,
                    'verified_by' => auth()->id(),
                    'verified_at' => Carbon::now(),
                ]
            );
            $sip->status = $sip->determineStatus();
            $sip->save();

            $imported++;
        }

        return redirect()->route('dokters.index')->with('success', "Berhasil mengimpor {$imported} data dokter baru.");
    }
}
