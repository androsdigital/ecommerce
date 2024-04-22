<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'price_before_discount',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('front_thumb')
            ->fit(Fit::Crop, 450, 300);

        $this->addMediaConversion('front_large')
            ->fit(Fit::Crop, 600);
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    protected function priceBeforeDiscount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function size(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'product_size')->withPivot('quantity');
    }

    public function productSizes(): HasMany
    {
        return $this->hasMany(ProductSize::class);
    }
}
