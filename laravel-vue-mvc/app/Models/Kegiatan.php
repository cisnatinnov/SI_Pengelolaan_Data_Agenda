<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kegiatan',
        'tempat_kegiatan',
        'tanggal_kegiatan',
        'uraian_kegiatan',
        'realisasi_pelaksanaan',
        'keterangan',
        'status',
        'nama_penyusun',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kegiatan' => 'datetime',
        ];
    }

    /**
     * Get the attendance confirmations for this kegiatan.
     */
    public function kehadiran(): HasMany
    {
        return $this->hasMany(KegiatanKehadiran::class);
    }
}
