<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'mobile_number',
    'address',
    'area',
    'customer_type',
    'status',
    'current_balance',
    'last_payment_amount',
    'last_payment_at',
    'last_delivery_at',
])]
class Customer extends Model
{
    public const TYPES = [
        'daily_customer' => 'Daily Customer',
        'walk_in' => 'Walk-in Customer',
        'hotel' => 'Hotel',
        'restaurant' => 'Restaurant',
        'tea_stall' => 'Tea Stall',
        'bakery' => 'Bakery',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_balance' => 'decimal:2',
            'last_payment_amount' => 'decimal:2',
            'last_payment_at' => 'datetime',
            'last_delivery_at' => 'datetime',
        ];
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->customer_type] ?? $this->customer_type;
    }
}
