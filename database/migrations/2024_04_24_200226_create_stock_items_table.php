<?php

use App\Models\Address;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Product::class)->constrained();
            $table->foreignIdFor(Color::class)->constrained();
            $table->foreignIdFor(Size::class)->constrained();
            $table->foreignIdFor(Address::class)->constrained();

            $table->string('sku', 10)->unique();
            $table->integer('quantity');
            $table->unsignedBigInteger('price_before_discount');
            $table->unsignedBigInteger('discount');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
