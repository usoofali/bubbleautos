<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'invoice_number',
        'subtotal',
        'discount',
        'total',
        'paid',
        'balance',
        'currency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'float',
            'discount' => 'float',
            'total' => 'float',
            'paid' => 'float',
            'balance' => 'float',
            'status' => InvoiceStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function recalculate(): self
    {
        $subtotal = (float) $this->items()->sum('amount');
        $discount = (float) $this->discount;
        $total = max(0, $subtotal - $discount);
        $paid = (float) $this->payments()->sum('amount');
        $balance = max(0, $total - $paid);

        $status = InvoiceStatus::PENDING;
        if ($paid >= $total && $total > 0) {
            $status = InvoiceStatus::PAID;
        } elseif ($paid > 0) {
            $status = InvoiceStatus::PARTIALLY_PAID;
        }

        $this->update([
            'subtotal' => $subtotal,
            'total' => $total,
            'paid' => $paid,
            'balance' => $balance,
            'status' => $status,
        ]);

        return $this;
    }
}
