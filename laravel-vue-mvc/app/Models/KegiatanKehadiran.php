<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanKehadiran extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kegiatan_id',
        'user_id',
        'status',
    ];

    /**
     * Get the kegiatan that this attendance belongs to.
     */
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    /**
     * Get the user that confirmed this attendance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}