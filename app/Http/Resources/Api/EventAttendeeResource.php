<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class EventAttendeeResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'event_id' => $this->event_id,
      'user_id' => $this->user_id,
      'status' => $this->status,
      'ticket_code' => $this->ticket_code,
      'registration_date' => $this->registration_date,
      'check_in_time' => $this->check_in_time,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'event' => new EventResource($this->whenLoaded('event')),
    ];
  }
}
