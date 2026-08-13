<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuratKegiatan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'surat_kegiatan';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'surat_undangan_id',
        'tanggal',
        'nomor_surat',
        'asal_surat',
        'perihal',
        'kepada',
        'pembawa_surat',
        'tandatangan_penerima',
        'tandatangan_dituju',
        'keterangan',
        'alasan',
    ];

    /**
     * Get the surat undangan that generated this surat kegiatan.
     */
    public function suratUndangan()
    {
        return $this->belongsTo(SuratUndangan::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
        ];
    }
}