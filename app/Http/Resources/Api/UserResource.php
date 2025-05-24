<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'email' => $this->email,
      'user_type' => $this->user_type,
      'is_active' => $this->is_active,
      'is_verified' => $this->is_verified,
      'phone_number' => $this->phone_number,
      'profile_picture' => $this->profile_picture,
      'bio' => $this->bio,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}
