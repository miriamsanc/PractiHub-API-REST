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

        $categories = Category::all();
            
        $empresa = User::factory()->create([
            'name' => 'Tech Solutions SL',
            'email' => 'empresa@test.com',
            'password' => bcrypt('password123'), 
            'role' => 'company',
        ]);
        $empresas = User::factory(4)->create(['role' => 'company']);

        $empresasCollection = collect([$empresa])
            ->merge($empresas);

        $estudiante = User::factory()->create([
            'name' => 'Juan Estudiante',
            'email' => 'estudiante@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);
        $estudiantes = User::factory(9)->create(['role' => 'student']);

        $estudiantesCollection = collect([$estudiante])
            ->merge($estudiantes);



        $offers = Offer::factory(10)->make()->each(function ($offer) use ($empresasCollection, $categories) {
            $offer->user_id = $empresasCollection->random()->id;
            $offer->category_id = $categories->random()->id;

            $offer->save();
        });

        // La migración tiene:         
        //unique(['user_id', 'offer_id'])         
        //Por eso no podemos seleccionar estudiante y oferta aleatoriamente sin controlar duplicados         
        //Generamos todas las combinaciones posibles y escogemos 15
         
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


        foreach ($combinations as $combination) {
            Application::factory()->create([
                'user_id' => $combination['user_id'],
                'offer_id' => $combination['offer_id'],
                'status' => 'pending',
            ]);
        }
    }
}


