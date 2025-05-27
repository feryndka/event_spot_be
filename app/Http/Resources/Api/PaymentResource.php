<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'user' => [
        'id' => $this->user->id,
        'name' => $this->user->name
      ],
      'amount' => $this->amount,
      'payment_method' => $this->payment_method,
      'status' => $this->status,
      'payment_date' => $this->created_at->format('Y-m-d H:i:s'),
      'transaction_id' => $this->transaction_id,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at
    ];
  }
}
