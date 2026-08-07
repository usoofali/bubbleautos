<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'vin',
        'make',
        'model',
        'year',
        'color',
        'customer_id',
        'shipping_line',
        'destination',
        'status',
        'expected_arrival',
        'pictures',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'status' => ShipmentStatus::class,
            'expected_arrival' => 'date:Y-m-d',
            'pictures' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class)->latest();
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(TimelineEvent::class)->orderBy('created_at', 'desc');
    }

    public function scopeSearchByVin(Builder $query, string $vin): Builder
    {
        return $query->where('vin', 'LIKE', '%'.trim($vin).'%');
    }

    public function scopeGlobalSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);

        return $query->where('vin', 'LIKE', "%{$term}%")
            ->orWhere('order_number', 'LIKE', "%{$term}%")
            ->orWhere('make', 'LIKE', "%{$term}%")
            ->orWhere('model', 'LIKE', "%{$term}%")
            ->orWhereHas('customer', function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('phone', 'LIKE', "%{$term}%");
            });
    }

    public static function generateNextOrderNumber(): string
    {
        $prefix = Setting::get('order_prefix', 'BA-');
        $lastOrder = static::withTrashed()->orderBy('id', 'desc')->first();
        $nextId = $lastOrder ? $lastOrder->id + 1 : 1;

        return sprintf('%s%05d', $prefix, $nextId);
    }
}
