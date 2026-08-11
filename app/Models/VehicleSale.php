<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use NumberFormatter;

class VehicleSale extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicle_sales';

    protected $fillable = [
        'sale_number',
        'customer_name',
        'customer_phone',
        'customer_address',
        'vehicle_make',
        'vehicle_model',
        'vehicle_year',
        'vehicle_vin',
        'vehicle_color',
        'vehicle_description',
        'sale_date',
        'sale_amount',
        'amount_paid',
        'payment_method',
        'amount_in_words',
        'notes',
        'created_by',
    ];

    protected $appends = [
        'total_paid',
        'balance_due',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'sale_amount' => 'float',
            'amount_paid' => 'float',
        ];
    }

    /**
     * Relationship to payment receipts.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(VehicleSalePayment::class, 'vehicle_sale_id')->oldest('id');
    }

    /**
     * Get calculated total paid from all payment receipts.
     */
    public function getTotalPaidAttribute(): float
    {
        if ($this->relationLoaded('payments') && $this->payments->count() > 0) {
            return (float) $this->payments->sum('amount_paid');
        }

        $paymentsSum = $this->payments()->sum('amount_paid');

        return $paymentsSum > 0 ? (float) $paymentsSum : (float) $this->amount_paid;
    }

    /**
     * Get calculated balance due.
     */
    public function getBalanceDueAttribute(): float
    {
        return max(0, (float) $this->sale_amount - $this->total_paid);
    }

    /**
     * Get calculated payment status ('paid', 'partially_paid', 'unpaid').
     */
    public function getPaymentStatusAttribute(): string
    {
        $paid = $this->total_paid;
        $total = (float) $this->sale_amount;

        if ($paid >= $total && $total > 0) {
            return 'paid';
        }

        if ($paid > 0) {
            return 'partially_paid';
        }

        return 'unpaid';
    }

    /**
     * Relationship to staff creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to search sales by customer, vehicle, VIN, or sale number.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('sale_number', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%")
                ->orWhere('customer_phone', 'like', "%{$term}%")
                ->orWhere('vehicle_make', 'like', "%{$term}%")
                ->orWhere('vehicle_model', 'like', "%{$term}%")
                ->orWhere('vehicle_vin', 'like', "%{$term}%");
        });
    }

    /**
     * Convert numeric amount to words in Nigerian Naira & Kobo.
     */
    public static function convertAmountToWords(float $amount): string
    {
        $whole = (int) floor($amount);
        $fraction = (int) round(($amount - $whole) * 100);

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            $wholeWords = ucwords($formatter->format($whole));
            $words = $wholeWords.' Naira';

            if ($fraction > 0) {
                $fractionWords = ucwords($formatter->format($fraction));
                $words .= ' and '.$fractionWords.' Kobo';
            } else {
                $words .= ' Only';
            }

            return $words;
        }

        return number_format($amount, 2).' Naira Only';
    }

    /**
     * Generate next sequential sale number (e.g. 0001).
     */
    public static function generateNextSaleNumber(): string
    {
        $maxId = static::withTrashed()->max('id') ?? 0;
        $nextId = $maxId + 1;

        return str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}
