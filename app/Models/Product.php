<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'features',
        'comments',
    ];

    protected $casts = [
        'features' => 'array',
        'comments' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }

    //    public function sizes(): HasManyThrough
    //    {
    //        return $this->hasManyThrough(Size::class, StockItem::class);
    //    }
    //
    //    public function colors(): HasManyThrough
    //    {
    //        return $this->hasManyThrough(Color::class, StockItem::class);
    //    }
}
