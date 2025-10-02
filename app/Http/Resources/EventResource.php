<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'start' => $this->start_date->toISOString(),
            'end' => $this->end_date?->toISOString(),
            'color' => $this->color,
            'is_deadline' => $this->is_deadline,
            'notes' => $this->notes,
            'links' => $this->links,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
