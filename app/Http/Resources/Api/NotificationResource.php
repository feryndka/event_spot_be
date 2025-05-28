<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'user_id' => $this->user_id,
      'title' => $this->title,
      'content' => $this->content,
      'type' => $this->type,
      'data' => $this->data,
      'is_read' => (bool) $this->is_read,
      'created_at' => $this->created_at->format('Y-m-d H:i:s')
    ];
  }
}
