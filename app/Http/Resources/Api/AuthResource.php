<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'user' => new UserResource($this['user']),
      'token' => $this['token'],
      'token_type' => 'Bearer',
    ];
  }
}
