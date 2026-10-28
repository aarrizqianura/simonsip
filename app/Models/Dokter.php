<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dokter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip_nik',
        'nama_lengkap',
        'no_whatsapp',
        'ksm_id',
        'spesialisasi_id',
        'no_str',
        'is_str_seumur_hidup',
        'tgl_kadaluarsa_str',
    ];

    protected function casts(): array
    {
        return [
            'is_str_seumur_hidup' => 'boolean',
            'tgl_kadaluarsa_str' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ksm(): BelongsTo
    {
        return $this->belongsTo(Ksm::class);
    }

    public function spesialisasi(): BelongsTo
    {
        return $this->belongsTo(Spesialisasi::class);
    }

    public function sipRecords(): HasMany
    {
        return $this->hasMany(SipRecord::class)->orderBy('tempat_praktik_ke')->orderByDesc('tgl_kadaluarsa');
    }

    /**
     * Primary SIP at RSUD Dr. H. Moch. Ansari Saleh (Praktik ke-1)
     */
    public function primarySip()
    {
        return $this->hasOne(SipRecord::class)->where('tempat_praktik_ke', 1)->latestOfMany();
    }

    /**
     * Get the most urgent or current valid status across SIP records
     */
    public function getOverallStatusAttribute(): string
    {
        $sips = $this->sipRecords;
        if ($sips->isEmpty()) {
            return 'expired';
        }

        // Check if there is pending verification
        if ($sips->contains('status', 'menunggu_verifikasi')) {
            return 'menunggu_verifikasi';
        }

        // Check worst severity: expired > mendesak > warning > aktif
        if ($sips->contains('status', 'expired')) {
            return 'expired';
        }
        if ($sips->contains('status', 'mendesak')) {
            return 'mendesak';
        }
        if ($sips->contains('status', 'warning')) {
            return 'warning';
        }

        return 'aktif';
    }

    /**
     * Get STR Status string
     */
    public function getStrStatusTextAttribute(): string
    {
        if ($this->is_str_seumur_hidup) {
            return 'Seumur Hidup';
        }

        if (!$this->tgl_kadaluarsa_str) {
            return 'Tidak Ditentukan';
        }

        $now = Carbon::now();
        $diff = $now->diffInDays($this->tgl_kadaluarsa_str, false);

        if ($diff < 0) {
            return 'Kadaluarsa (' . $this->tgl_kadaluarsa_str->translatedFormat('d F Y') . ')';
        }

        return 'Berlaku s/d ' . $this->tgl_kadaluarsa_str->translatedFormat('d F Y');
    }
}
