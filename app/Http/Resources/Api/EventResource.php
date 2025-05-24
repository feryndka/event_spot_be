<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'slug' => $this->slug,
      'description' => $this->description,
      'is_ai_generated' => $this->is_ai_generated,
      'poster_image' => $this->poster_image,
      'promotor' => new UserResource($this->whenLoaded('promotor')),
      'category' => new CategoryResource($this->whenLoaded('category')),
      'location_name' => $this->location_name,
      'address' => $this->address,
      'latitude' => $this->latitude,
      'longitude' => $this->longitude,
      'start_date' => $this->start_date,
      'end_date' => $this->end_date,
      'registration_start' => $this->registration_start,
      'registration_end' => $this->registration_end,
      'is_free' => $this->is_free,
      'price' => $this->price,
      'max_attendees' => $this->max_attendees,
      'is_published' => $this->is_published,
      'is_featured' => $this->is_featured,
      'is_approved' => $this->is_approved,
      'views_count' => $this->views_count,
      'images' => EventImageResource::collection($this->whenLoaded('images')),
      'tags' => EventTagResource::collection($this->whenLoaded('tags')),
      'statistics' => new StatisticResource($this->whenLoaded('statistics')),
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}
