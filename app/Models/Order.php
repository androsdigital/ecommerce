<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'address_id',
        'customer_id',
        'number',
        'total_price_before_discount',
        'total_discount',
        'total_shipping_price',
        'total_quantity',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    /**
     * @return BelongsTo<Customer, Order>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<OrderItem>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return BelongsTo<Address, Order>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
