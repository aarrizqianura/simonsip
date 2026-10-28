<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 2. Tabel KSM (Kelompok Staf Medis)
        Schema::create('ksm', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ksm', 150);
            $table->timestamps();
        });

        // 3. Tabel Spesialisasi
        Schema::create('spesialisasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_spesialisasi', 150);
            $table->timestamps();
        });

        // 4. Tabel Dokters (Master Data Profil Dokter)
        Schema::create('dokters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nip_nik', 50)->unique();
            $table->string('nama_lengkap', 255);
            $table->string('no_whatsapp', 20);
            $table->foreignId('ksm_id')->constrained('ksm');
            $table->foreignId('spesialisasi_id')->constrained('spesialisasi');
            $table->string('no_str', 100);
            $table->boolean('is_str_seumur_hidup')->default(true);
            $table->date('tgl_kadaluarsa_str')->nullable();
            $table->timestamps();
        });

        // 5. Tabel Sip_Records (Record Multi-SIP & Status Masa Berlaku)
        Schema::create('sip_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokter_id')->constrained('dokters')->cascadeOnDelete();
            $table->string('no_sip', 100);
            $table->unsignedTinyInteger('tempat_praktik_ke')->default(1);
            $table->date('tgl_terbit');
            $table->date('tgl_kadaluarsa');
            $table->string('file_sip_path', 255);
            $table->enum('status', ['aktif', 'warning', 'mendesak', 'expired', 'menunggu_verifikasi'])->default('aktif');
            $table->boolean('is_verified')->default(true);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
        });

        // 6. Tabel Notification_Logs (Log WhatsApp Blast)
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sip_record_id')->constrained('sip_records')->cascadeOnDelete();
            $table->string('recipient_phone', 20);
            $table->string('channel', 50)->default('whatsapp');
            $table->enum('message_type', [
                'account_created',
                'early_warning',
                'urgent',
                'verifikasi_approved',
                'verifikasi_rejected'
            ]);
            $table->text('payload');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('sip_records');
        Schema::dropIfExists('dokters');
        Schema::dropIfExists('spesialisasi');
        Schema::dropIfExists('ksm');
    }
};
