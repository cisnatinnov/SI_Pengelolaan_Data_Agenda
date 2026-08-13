<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuratUndangan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tanggal',
        'nomor_surat',
        'asal_surat',
        'perihal',
        'kepada',
        'tanggal_pelaksanaan',
        'tempat_pelaksanaan',
        'pembawa_surat',
        'tandatangan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
            'tanggal_pelaksanaan' => 'datetime',
        ];
    }
}