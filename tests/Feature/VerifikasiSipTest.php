<?php

namespace Tests\Feature;

use App\Models\Dokter;
use App\Models\Ksm;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use App\Models\Spesialisasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VerifikasiSipTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminAndPendingSip(): array
    {
        $admin = User::create([
            'name' => 'Admin SDM Kepegawaian',
            'username' => 'adminsdm_test',
            'email' => 'sdm@ansarisaleh.local',
            'password' => Hash::make('password'),
            'role' => 'admin_sdm',
        ]);

        $dokterUser = User::create([
            'name' => 'dr. Bambang Prasetyo, Sp.JP',
            'username' => '198402122009011005',
            'email' => 'bambang@ansarisaleh.local',
            'password' => Hash::make('password'),
            'role' => 'dokter',
        ]);

        $ksm = Ksm::create(['nama_ksm' => 'KSM Jantung & Pembuluh Darah']);
        $spes = Spesialisasi::create(['nama_spesialisasi' => 'Dokter Spesialis Jantung (Sp.JP)']);

        $dokter = Dokter::create([
            'user_id' => $dokterUser->id,
            'nip_nik' => '198402122009011005',
            'nama_lengkap' => 'dr. Bambang Prasetyo, Sp.JP',
            'no_whatsapp' => '081299887711',
            'ksm_id' => $ksm->id,
            'spesialisasi_id' => $spes->id,
            'no_str' => '3314100520156789',
            'is_str_seumur_hidup' => true,
        ]);

        $sipRecord = SipRecord::create([
            'dokter_id' => $dokter->id,
            'no_sip' => '503/0991/SIP-D/DPMPTSP/2026',
            'tempat_praktik_ke' => 1,
            'tgl_terbit' => Carbon::today()->subDays(2),
            'tgl_kadaluarsa' => Carbon::today()->addYears(5),
            'file_sip_path' => 'sip_documents/sample_sip_ansari.pdf',
            'status' => 'menunggu_verifikasi',
            'is_verified' => false,
        ]);

        return [$admin, $dokter, $sipRecord];
    }

    public function test_pdf_preview_and_download_endpoints_return_pdf(): void
    {
        [$admin, $dokter, $sipRecord] = $this->createAdminAndPendingSip();

        $previewResponse = $this->actingAs($admin)->get(route('verifikasi.preview', $sipRecord));
        $previewResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $previewResponse->headers->get('content-type'));

        $downloadResponse = $this->actingAs($admin)->get(route('verifikasi.download', $sipRecord));
        $downloadResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $downloadResponse->headers->get('content-type'));
    }

    public function test_reject_requires_reason_and_sends_whatsapp_notification(): void
    {
        [$admin, $dokter, $sipRecord] = $this->createAdminAndPendingSip();

        // Attempt without reason should fail validation
        $invalidResponse = $this->actingAs($admin)->post(route('verifikasi.reject', $sipRecord), [
            'alasan_penolakan' => '',
        ]);
        $invalidResponse->assertSessionHasErrors('alasan_penolakan');

        // Valid rejection reason
        $alasan = 'Hasil scan dokumen buram dan masa berlaku tidak sesuai dengan fisik SIP.';
        $response = $this->actingAs($admin)->post(route('verifikasi.reject', $sipRecord), [
            'alasan_penolakan' => $alasan,
        ]);

        $response->assertRedirect(route('verifikasi.index'));
        $response->assertSessionHas('wa_notification');

        $sipRecord->refresh();
        $this->assertFalse($sipRecord->is_verified);
        $this->assertTrue($sipRecord->is_rejected);
        $this->assertEquals($alasan, $sipRecord->catatan_verifikasi);

        $waLog = NotificationLog::where('sip_record_id', $sipRecord->id)
            ->where('message_type', 'verifikasi_rejected')
            ->first();

        $this->assertNotNull($waLog);
        $this->assertStringContainsString($alasan, $waLog->payload);
    }

    public function test_approve_activates_sip_and_sends_whatsapp_notification(): void
    {
        [$admin, $dokter, $sipRecord] = $this->createAdminAndPendingSip();

        $response = $this->actingAs($admin)->post(route('verifikasi.approve', $sipRecord), [
            'catatan' => 'Berkas lengkap dan tervalidasi.',
        ]);

        $response->assertRedirect(route('verifikasi.index'));
        $response->assertSessionHas('wa_notification');

        $sipRecord->refresh();
        $this->assertTrue($sipRecord->is_verified);
        $this->assertEquals('aktif', $sipRecord->status);

        $waLog = NotificationLog::where('sip_record_id', $sipRecord->id)
            ->where('message_type', 'verifikasi_approved')
            ->first();

        $this->assertNotNull($waLog);
        $this->assertStringContainsString($sipRecord->no_sip, $waLog->payload);
    }

    public function test_empty_state_shown_when_queue_is_zero(): void
    {
        [$admin, $dokter, $sipRecord] = $this->createAdminAndPendingSip();
        $sipRecord->delete();

        $response = $this->actingAs($admin)->get(route('verifikasi.index'));
        $response->assertStatus(200);
        $response->assertSee('Semua antrean pengajuan SIP telah selesai diverifikasi');
    }
}

