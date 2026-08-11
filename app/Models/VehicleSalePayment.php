<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleSalePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_sale_id',
        'receipt_number',
        'amount_paid',
        'payment_date',
        'payment_method',
        'amount_in_words',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function vehicleSale(): BelongsTo
    {
        return $this->belongsTo(VehicleSale::class, 'vehicle_sale_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate next sequential receipt number (e.g. 0001).
     */
    public static function generateNextReceiptNumber(): string
    {
        $maxId = static::max('id') ?? 0;
        $nextId = $maxId + 1;

        return str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}
