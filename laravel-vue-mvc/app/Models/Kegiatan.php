<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
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
}
