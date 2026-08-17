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
        'source',
        'read_at',
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
            'read_at' => 'datetime',
        ];
    }

    /**
     * Scope the query to only notification-type pengingat
     * (auto-generated from surat or kegiatan, not manual).
     */
    public function scopeNotifications($query)
    {
        return $query->where('source', '!=', 'manual');
    }

    /**
     * Scope the query to only unread notification-type pengingat.
     */
    public function scopeUnread($query)
    {
        return $query->notifications()->whereNull('read_at');
    }

    /**
     * Get the user that owns the pengingat.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
