<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Application;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
        CategorySeeder::class,
        ]);
    
        Category::factory(5)->create();
        
        $empresa = User::factory()->create([
            'name' => 'Tech Solutions SL',
            'email' => 'empresa@test.com',
            'password' => bcrypt('password123'), 
            'role' => 'company',
        ]);
        
        $estudiante = User::factory()->create([
            'name' => 'Juan Estudiante',
            'email' => 'estudiante@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);
        
        Offer::factory(10)->create();
        
        Application::factory(15)->create();

        //User::factory()->create([
            //'name' => 'Test User',
            //'email' => 'test@example.com',
        //]);
    }
}
