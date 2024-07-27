<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class StockItem extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'stock_items';

    protected $fillable = [
        'sku',
        'product_id',
        'size_id',
        'color_id',
        'address_id',
        'quantity',
        'price',
        'price_before_discount',
        'discount',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('front_thumb')
            ->fit(Fit::Crop, 450, 300);

        $this->addMediaConversion('front_large')
            ->fit(Fit::Crop, 600);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
