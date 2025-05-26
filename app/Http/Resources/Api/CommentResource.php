<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'event_id' => $this->event_id,
      'content' => $this->content,
      'is_approved' => $this->is_approved,
      'user' => [
        'id' => $this->user->id,
        'name' => $this->user->name,
        'user_type' => $this->user->user_type,
        'profile_image' => $this->user->profile_image
      ],
      'parent_id' => $this->parent_id,
      'parent_comment' => new CommentResource($this->whenLoaded('parent')),
      'replies' => CommentResource::collection($this->whenLoaded('replies')),
      'replies_count' => $this->replies()->count(),
      'created_at' => $this->created_at->diffForHumans(),
      'updated_at' => $this->updated_at->diffForHumans()
    ];
  }
}
