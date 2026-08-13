<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Surat extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'surat';

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

    /**
     * Get the disposisi records linked to this surat.
     */
    public function disposisis(): HasMany
    {
        return $this->hasMany(Disposisi::class);
    }
}
