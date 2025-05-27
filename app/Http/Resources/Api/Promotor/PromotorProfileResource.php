<?php

namespace App\Http\Resources\Api\Promotor;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\UserResource;

class PromotorProfileResource extends JsonResource
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
      'name' => $this->name,
      'email' => $this->email,
      'phone_number' => $this->phone_number,
      'bio' => $this->bio,
      'user_type' => $this->user_type,
      'profile' => [
        'company_name' => $this->promotorDetail->company_name ?? null,
        'company_logo' => $this->promotorDetail->company_logo ? asset('storage/' . $this->promotorDetail->company_logo) : null,
        'description' => $this->promotorDetail->description ?? null,
        'website' => $this->promotorDetail->website ?? null,
        'social_media' => $this->promotorDetail->social_media ?? null,
        'verification_staus' => $this->promotorDetail->verification_status ?? 'pending',
      ],
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at
    ];
  }
}
