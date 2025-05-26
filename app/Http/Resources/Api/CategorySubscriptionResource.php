<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\UserResource;
use App\Http\Resources\Api\CategoryResource;

class CategorySubscriptionResource extends JsonResource
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
      'user' => new UserResource($this->user),
      'category' => new CategoryResource($this->category),
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at
    ];
  }
}
