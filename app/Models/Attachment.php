<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'message_id',
        'memory_id',
        'file_path',
        'original_name',
        'size',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function memory(): BelongsTo
    {
        return $this->belongsTo(Memory::class);
    }
}
