<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'mobile_number',
    'address',
    'status',
    'paid_amount',
])]
class Supplier extends Model
{
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
            'paid_amount' => 'decimal:2',
        ];
    }

    public function milkCollections(): HasMany
    {
        return $this->hasMany(MilkCollection::class);
    }

    public function totalMilk(): float
    {
        return (float) $this->milkCollections()->sum('quantity');
    }

    public function totalAmount(): float
    {
        return (float) $this->milkCollections()->sum('amount');
    }

    public function remainingAmount(): float
    {
        return $this->totalAmount() - (float) $this->paid_amount;
    }
}
