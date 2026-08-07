<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_id',
        'filename',
        'file_path',
        'file_size',
        'mime_type',
    ];

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }
}
