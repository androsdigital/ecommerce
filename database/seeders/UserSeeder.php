<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create(['email' => 'master@ecommerce.com']);
        User::factory(100)->create();
    }
}
