<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'user_id' => $this->user_id,
      'email_notifications' => $this->email_notifications,
      'push_notifications' => $this->push_notifications,
      'event_reminders' => $this->event_reminders,
      'preferred_categories' => $this->preferred_categories,
      'preferred_locations' => $this->preferred_locations,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at
    ];
  }
}
