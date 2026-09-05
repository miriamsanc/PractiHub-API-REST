<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'location'    => $this->location,
            'is_active'   => (bool) $this->is_active,
            
            // Si la relación está cargada, la formateamos, si no, no la mostramos
            'category'    => $this->whenLoaded('category', function () {
                return $this->category->name; 
            }),

            'company' => $this->whenLoaded('company', fn() => $this->company->name),
            
            // Formatea fechas para que el frontend no sufra
            'published_at' => $this->created_at->format('d-m-Y'), 
        ];
    }
}
