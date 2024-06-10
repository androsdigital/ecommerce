<?php

use App\Enums\AddressType;
use App\Enums\StreetType;
use App\Models\City;
use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(City::class)->constrained();
            $table->foreignIdFor(Customer::class)->constrained();

            $table->enum('street_type', StreetType::values())->default(StreetType::Calle);
            $table->string('street_number', 255);
            $table->string('first_number', 255)->nullable();
            $table->string('second_number', 255)->nullable();
            $table->string('apartment', 255)->nullable();
            $table->enum('type', AddressType::values())->default(AddressType::Urban);
            $table->string('phone', 255)->nullable();
            $table->text('observation')->nullable();
            $table->geometry('location')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
