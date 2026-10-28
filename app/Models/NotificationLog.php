<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory;

    protected $table = 'notification_logs';

    protected $fillable = [
        'sip_record_id',
        'recipient_phone',
        'channel',
        'message_type',
        'payload',
        'status',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function sipRecord(): BelongsTo
    {
        return $this->belongsTo(SipRecord::class);
    }

    public function getMessageTypeLabelAttribute(): string
    {
        return match ($this->message_type) {
            'account_created' => 'Notifikasi Akun Baru',
            'early_warning' => 'Early Warning (61-90 Hari)',
            'urgent' => 'Peringatan Mendesak (≤60 Hari)',
            'verifikasi_approved' => 'Verifikasi Disetujui',
            'verifikasi_rejected' => 'Verifikasi Ditolak',
            default => ucfirst($this->message_type),
        };
    }
}
