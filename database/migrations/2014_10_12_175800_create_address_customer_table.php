<?php

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('address_customer', function (Blueprint $table) {
            $table->foreignIdFor(Address::class)->constrained()->CascadeOnDelete();
            $table->foreignIdFor(Customer::class)->constrained()->CascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_customer');
    }
};
