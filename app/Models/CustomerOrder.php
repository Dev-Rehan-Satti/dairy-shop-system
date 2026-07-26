<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_id',
    'order_type',
    'order_date',
    'morning_quantity',
    'evening_quantity',
    'status',
])]
class CustomerOrder extends Model
{
    public const TYPES = [
        'recurring' => 'Recurring Order',
        'one_time' => 'One Time Order',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'morning_quantity' => 'decimal:2',
            'evening_quantity' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->order_type] ?? $this->order_type;
    }
}
