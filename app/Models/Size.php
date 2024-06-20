<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Size extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(Product::class, StockItem::class);
    }

    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }

    public function colors(): HasManyThrough
    {
        return $this->hasManyThrough(Color::class, StockItem::class);
    }
}
