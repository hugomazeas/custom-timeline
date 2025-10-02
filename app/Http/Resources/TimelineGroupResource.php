<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimelineGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at->toISOString(),
            'rows' => $this->timelineRows->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'events' => EventResource::collection($row->events)->resolve(),
                ];
            })->toArray(),
        ];
    }
}
