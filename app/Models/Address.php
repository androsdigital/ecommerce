<?php

namespace App\Models;

use App\Enums\AddressType;
use App\Enums\StreetType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
        'apartment_number',
        'type',
        'phone',
        'observation',
        'location',
    ];

    protected $casts = [
        'street_type' => StreetType::class,
        'type'        => AddressType::class,
    ];

    /**
     * @return BelongsTo<City, Address>
     */
    public function city(): belongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
