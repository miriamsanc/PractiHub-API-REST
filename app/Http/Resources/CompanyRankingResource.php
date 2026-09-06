<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyRankingResource extends JsonResource
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
            'name' => $this->name,
            'accepted_count' => $this->accepted_count,
            'rejected_count' => $this->rejected_count,
            'total_resolved' => $this->accepted_count + $this->rejected_count,
            'acceptance_rate' => $this->acceptance_rate,
        ];
    }
}
