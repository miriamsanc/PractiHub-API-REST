<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'applied_at' => $this->created_at->format('Y-m-d H:i:s'),
 
            // Resumen de la oferta: lo justo para identificarla en una lista
            'offer' => $this->whenLoaded('offer', function () {
                return [
                    'id' => $this->offer->id,
                    'title' => $this->offer->title,
                    'company' => $this->offer->company?->name,
                ];
            }),
 
            // Resumen del estudiante (solo relevante cuando lo consulta la empresa)
            'student' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
        ];
    }
}
