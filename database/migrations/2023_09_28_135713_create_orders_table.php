<?php

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Customer::class)->constrained();
            $table->foreignIdFor(Address::class)->nullable();

            $table->string('number', 31)->unique();
            $table->unsignedBigInteger('total_price');
            $table->unsignedBigInteger('total_price_before_discount');
            $table->unsignedBigInteger('total_items_discount');
            $table->unsignedBigInteger('discount');
            $table->unsignedBigInteger('total_discount');
            $table->unsignedBigInteger('total_shipping_price');
            $table->unsignedInteger('total_quantity');
            $table->enum('status', OrderStatus::values())->default(OrderStatus::Processing);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
