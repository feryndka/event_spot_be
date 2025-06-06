<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class VerificationResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'email' => $this->email,
      'phone_number' => $this->phone_number,
      'user_type' => $this->user_type,
      'is_verified' => $this->is_verified,
      'is_active' => $this->is_active,
      'promotor_detail' => $this->when($this->promotorDetail, function () {
        return [
          'company_name' => $this->promotorDetail->company_name,
          'description' => $this->promotorDetail->description,
          'website' => $this->promotorDetail->website,
          'verification_status' => $this->promotorDetail->verification_status,
          'verification_document' => $this->promotorDetail->verification_document,
        ];
      }),
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}
