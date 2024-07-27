<?php

namespace App\Models;

use App\Enums\StreetType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';

    protected $fillable = [
        'city_id',
        'street_type',
        'street_number',
        'first_number',
        'second_number',
        'apartment',
        'building',
        'phone',
        'full_address',
        'observation',
        'location',
    ];

    protected $casts = [
        'street_type' => StreetType::class,
    ];

    /**
     * @return BelongsTo<City, Address>
     */
    public function city(): belongsTo
    {
        return $this->belongsTo(City::class);
    }

    //    public function orders(): HasMany
    //    {
    //        return $this->hasMany(Order::class);
    //    }
    //
    //    public function stockItems(): HasMany
    //    {
    //        return $this->hasMany(StockItem::class);
    //    }
    //
    //    public function customers(): BelongsToMany
    //    {
    //        return $this->belongsToMany(Customer::class);
    //    }
}
