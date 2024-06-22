<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create(['email' => 'admin@admin.com']);
        User::factory()->create(['email' => 'manolodelmal@dosporuno.com']);
        User::factory(100)->create();
    }
}
