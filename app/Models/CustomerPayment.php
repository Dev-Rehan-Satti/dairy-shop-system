<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_id',
    'receipt_number',
    'amount',
    'method',
    'payment_date',
    'account_details',
    'reference_number',
    'notes',
])]
class CustomerPayment extends Model
{
    public const METHODS = [
        'cash' => 'Cash',
        'bank' => 'Bank Transfer',
        'jazzcash' => 'JazzCash',
        'easypaisa' => 'Easypaisa',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }

    public function accountFieldLabel(): string
    {
        return match ($this->method) {
            'bank' => 'Bank Account Details',
            'jazzcash' => 'JazzCash Account Number',
            'easypaisa' => 'Easypaisa Account Number',
            default => 'Received By',
        };
    }

    public static function generateReceiptNumber(): string
    {
        return 'RCPT-'.str_pad((string) (self::count() + 1), 6, '0', STR_PAD_LEFT);
    }
}
