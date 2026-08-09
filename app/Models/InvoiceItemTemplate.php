<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItemTemplate extends Model
{
    protected $fillable = [
        'description',
        'default_amount',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
    ];
}
