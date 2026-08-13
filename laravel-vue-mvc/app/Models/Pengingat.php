<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengingat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'judul',
        'deskripsi',
        'tanggal_pengingat',
        'prioritas',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pengingat' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the pengingat.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
