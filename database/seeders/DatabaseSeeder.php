<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Application;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear el cliente de acceso personal para Laravel Passport
        Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'PractiHub Personal Access Client',
            '--provider' => 'users',
            '--no-interaction' => true,
        ]);

        // 2. Ejecutar Seeder de Categorías
        $this->call([
            CategorySeeder::class,
        ]);

        $categories = Category::all();
            
        // 3. Crear Usuarios (Empresas y Estudiantes)
        $empresa = User::factory()->create([
            'name' => 'Tech Solutions SL',
            'email' => 'empresa@test.com',
            'password' => bcrypt('Password123!'), 
            'role' => 'company',
        ]);
        $empresas = User::factory(4)->create(['role' => 'company']);

        $empresasCollection = collect([$empresa])->merge($empresas);

        $estudiante = User::factory()->create([
            'name' => 'Juan Estudiante',
            'email' => 'estudiante@test.com',
            'password' => bcrypt('Password123!'),
            'role' => 'student',
        ]);
        $estudiantes = User::factory(9)->create(['role' => 'student']);

        $estudiantesCollection = collect([$estudiante])->merge($estudiantes);

        // 4. Crear Ofertas asociadas a empresas y categorías
        $offers = Offer::factory(10)->make()->each(function ($offer) use ($empresasCollection, $categories) {
            $offer->user_id = $empresasCollection->random()->id;
            $offer->category_id = $categories->random()->id;
            $offer->save();
        });

        // 5. Crear Candidaturas únicas con estados variados (pending, accepted, rejected)
        $combinations = $estudiantesCollection
            ->flatMap(function ($student) use ($offers) {
                return $offers->map(function ($offer) use ($student) {
                    return [
                        'user_id' => $student->id,
                        'offer_id' => $offer->id,
                    ];
                });
            })
            ->shuffle()
            ->take(15);

        $statuses = ['pending', 'accepted', 'rejected'];

        foreach ($combinations as $combination) {
            Application::factory()->create([
                'user_id' => $combination['user_id'],
                'offer_id' => $combination['offer_id'],
                'status' => $statuses[array_rand($statuses)], // Asigna un estado al azar
            ]);
        }
    }
}


