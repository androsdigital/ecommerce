<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Color extends Model
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

    public function sizes(): HasManyThrough
    {
        return $this->hasManyThrough(Size::class, StockItem::class);
    }
}
