<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Post::factory(10)->create();
        Category::factory(10)->create();

        User::factory()->create([
            'name' => 'tuantq',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12341234'),
        ]);

        $this->call(RolePermissionSeeder::class);
    }
}
