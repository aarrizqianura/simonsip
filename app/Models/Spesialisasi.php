<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Spesialisasi extends Model
{
    use HasFactory;

    protected $table = 'spesialisasi';

    protected $fillable = [
        'nama_spesialisasi',
    ];

    public function dokters(): HasMany
    {
        return $this->hasMany(Dokter::class);
    }
}
