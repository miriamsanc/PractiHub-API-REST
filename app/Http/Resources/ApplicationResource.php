<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
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
            'user_id' => $this->user_id,
            'offer_id' => $this->offer_id,
            'status' => $this->status,
            'cv_link' => $this->cv_path, 
            'applied_at' => $this->created_at->format('Y-m-d H:i:s'),

            // Aqui solo adjunta estos datos si los hemos pedido con el with() en el controlador
            'student' => $this->whenLoaded('user'),
            'offer' => new OfferResource($this->whenLoaded('offer')),
        ];
    }
}
