<?php

namespace Database\Factories;

use App\Models\Offer;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Títulos de ofertas en español coherentes con las categorías
        $titles = [
            'Desarrollador/a Frontend React',
            'Desarrollador/a Backend Laravel',
            'Diseñador/a UI/UX Junior',
            'Especialista en Marketing Digital',
            'Técnico/a de Soporte Informático',
            'Asistente de Recursos Humanos',
            'Consultor/a Junior de Finanzas',
            'Gestor/a de Cuentas y Ventas',
            'Administrativo/a con Inglés',
            'Community Manager y Creador/a de Contenido',
            'Técnico/a de Logística y Almacén',
            'Analista de Datos Junior',
            'Recepcionista para Sector Turístico',
            'Educador/a Infantil'
        ];

        // Descripciones profesionales en español
        $descriptions = [
            'Buscamos un perfil proactivo y con ganas de aprender para incorporarse a nuestro equipo. Participarás en proyectos reales desde el primer día con la mentoría de un tutor asignado.',
            'Excelente oportunidad para estudiantes o recién graduados que deseen adquirir experiencia profesional en un entorno dinámico e innovador. Formación continua a cargo de la empresa.',
            'Te integrarás en un equipo multidisciplinar desempeñando tareas de apoyo, análisis y desarrollo de proyectos. Buscamos a alguien apasionado por su área y con orientación a resultados.',
            'Si buscas tu primera experiencia laboral en un ambiente joven y colaborativo, ¡esta es tu oportunidad! Ofrecemos horario flexible y posibilidad de crecimiento real dentro de la empresa.'
        ];

        // Ciudades principales de España
        $cities = [
            'Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Zaragoza', 
            'Málaga', 'Murcia', 'Bilbao', 'Alicante', 'Valladolid'
        ];
    
    
        return [
            'user_id' => User::factory()->create(['role' => 'company']), 
            'category_id' => Category::factory(), 
            //'category_id' => Category::inRandomOrder()->first()->id,
            'title' => fake()->randomElement($titles),
            'description' => fake()->randomElement($descriptions),
            'location' => fake()->randomElement($cities),
            'is_active' => fake()->boolean(80),
        ];
    }
}
