<?php

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
            $table->foreignIdFor(Customer::class)->nullable();

            $table->enum('street_type', StreetType::values())->default(StreetType::Calle);
            $table->string('street_number', 31);
            $table->string('first_number', 31)->nullable();
            $table->string('second_number', 31)->nullable();
            $table->string('apartment', 255)->nullable();
            $table->string('phone', 31)->nullable();
            $table->text('observation')->nullable();
            $table->geometry('location')->nullable();

            $table->string('full_address')->virtualAs(
                "CONCAT(street_type, ' ', street_number, ' # ', first_number, ' - ', second_number)"
            );

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
