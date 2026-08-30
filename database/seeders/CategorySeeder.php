<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Desarrollo Web',
            'Marketing',
            'Diseño',
            'RRHH',
            'Administración y finanzas',
            'Sanidad',
            'Ingeniería',
            'Turismo y hostelería',
            'Educación',
            'Construcción',
            'Transporte y logística',
            'Ventas',
            'Atención al cliente',
            'Otros'
        
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category
            ]);
        }
    }
}
