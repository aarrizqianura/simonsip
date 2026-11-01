<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SipRecord extends Model
{
    use HasFactory;

    protected $table = 'sip_records';

    protected $fillable = [
        'dokter_id',
        'no_sip',
        'tempat_praktik_ke',
        'tgl_terbit',
        'tgl_kadaluarsa',
        'file_sip_path',
        'status',
        'is_verified',
        'verified_by',
        'verified_at',
        'catatan_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tempat_praktik_ke' => 'integer',
            'tgl_terbit' => 'date',
            'tgl_kadaluarsa' => 'date',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class)->orderByDesc('created_at');
    }

    /**
     * Calculate remaining days from today until expiry date
     */
    public function getSisaHariAttribute(): int
    {
        if (!$this->tgl_kadaluarsa) {
            return 0;
        }

        $today = Carbon::today();
        return (int) $today->diffInDays($this->tgl_kadaluarsa, false);
    }

    /**
     * Compute and determine what status should be according to early warning rules
     */
    public function determineStatus(): string
    {
        if ($this->status === 'menunggu_verifikasi' || !$this->is_verified) {
            return 'menunggu_verifikasi';
        }

        $days = $this->sisa_hari;

        if ($days > 90) {
            return 'aktif'; // 🟢 Aman
        } elseif ($days >= 61) {
            return 'warning'; // 🟡 Peringatan (61–90 hari)
        } elseif ($days >= 31) {
            return 'mendesak'; // 🟠 Mendesak (31–60 hari)
        } else {
            return 'expired'; // 🔴 Kadaluarsa (<= 30 hari / expired)
        }
    }

    /**
     * Sync and update status in database based on current date
     */
    public function updateCalculatedStatus(): void
    {
        if ($this->status !== 'menunggu_verifikasi') {
            $newStatus = $this->determineStatus();
            if ($this->status !== $newStatus) {
                $this->update(['status' => $newStatus]);
            }
        }
    }

    /**
     * Check if this SIP record was reviewed and rejected by SDM
     */
    public function getIsRejectedAttribute(): bool
    {
        return !$this->is_verified && $this->verified_at !== null && $this->status !== 'menunggu_verifikasi';
    }

    /**
     * Status Label text in Indonesian
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->is_rejected) {
            return '🔴 Ditolak / Perlu Revisi';
        }

        return match ($this->status) {
            'aktif' => '🟢 Aman',
            'warning' => '🟡 Peringatan (61-90 hari)',
            'mendesak' => '🟠 Mendesak (31-60 hari)',
            'expired' => '🔴 Kadaluarsa (≤30 hari/Expired)',
            'menunggu_verifikasi' => '🔵 Menunggu Verifikasi SDM',
            default => ucfirst($this->status),
        };
    }

    /**
     * Status Badge HTML CSS class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->is_rejected) {
            return 'bg-rose-50 text-rose-700 border-rose-200 ring-rose-500/20';
        }

        return match ($this->status) {
            'aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-500/20',
            'warning' => 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-500/20',
            'mendesak' => 'bg-orange-50 text-orange-700 border-orange-200 ring-orange-500/20',
            'expired' => 'bg-rose-50 text-rose-700 border-rose-200 ring-rose-500/20',
            'menunggu_verifikasi' => 'bg-sky-50 text-sky-700 border-sky-200 ring-sky-500/20',
            default => 'bg-gray-50 text-gray-700 border-gray-200 ring-gray-500/20',
        };
    }
}
