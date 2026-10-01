<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_STATUS_SUCCEEDED = 'succeeded';

    public const PAYMENT_STATUS_FAILED = 'failed';

    public const PAYMENT_STATUS_PROCESSING = 'processing';

    /**
     * Only customer-supplied shipping fields are mass-assignable from HTTP.
     * Services use forceFill() for totals, status, payment, and timestamps.
     */
    protected $fillable = [
        'shipping_name',
        'shipping_line1',
        'shipping_city',
        'shipping_state',
        'shipping_postal',
        'shipping_country',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expires_at' => 'datetime',
            'stock_restored_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isExpiredPending(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function hasReleasedStock(): bool
    {
        return $this->stock_restored_at !== null;
    }
}
