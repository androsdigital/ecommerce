<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::factory()->count(100)->create()->each(function (Customer $customer) {
            $customer->addresses()->saveMany(Address::factory()->count(2)->create());
        });
    }
}
