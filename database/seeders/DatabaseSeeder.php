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
    
        
        $empresa = User::factory()->create([
            'name' => 'Tech Solutions SL',
            'email' => 'empresa@test.com',
            'password' => bcrypt('password123'), 
            'role' => 'company',
        ]);
        $empresas = User::factory(4)->create(['role' => 'company']);

        $estudiante = User::factory()->create([
            'name' => 'Juan Estudiante',
            'email' => 'estudiante@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);
        $estudiantes = User::factory(9)->create(['role' => 'student']);

        $categories = Category::all();

        $offers = Offer::factory(10)->make()->each(function ($offer) use ($empresa, $empresas, $categories) {
        $offer->user_id = collect([$empresa])->merge($empresas)->random()->id;
        $offer->category_id = $categories->random()->id;
        $offer->save();
        });
    
        Application::factory(15)->make()->each(function ($application) use ($estudiante, $estudiantes, $offers) {
        $application->user_id = collect([$estudiante])->merge($estudiantes)->random()->id;
        $application->offer_id = $offers->random()->id;
        $application->save();
        });
        
        

        
    }
}
