<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class EventImageResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'event_id' => $this->event_id,
      'image_path' => $this->image_path,
      'image_type' => $this->image_type,
      'is_primary' => $this->is_primary,
      'order' => $this->order,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}
