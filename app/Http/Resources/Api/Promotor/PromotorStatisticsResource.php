<?php

namespace App\Http\Resources\Api\Promotor;

use Illuminate\Http\Resources\Json\JsonResource;

class PromotorStatisticsResource extends JsonResource
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
      'total_followers' => $this['total_followers'],
      'total_events' => $this['total_events'],
      'total_attendees' => $this['total_attendees']
    ];
  }
}
