<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class StatisticResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'event_id' => $this->event_id,
      'page_views' => $this->page_views,
      'unique_visitors' => $this->unique_visitors,
      'engagement_rate' => $this->engagement_rate,
      'click_through_rate' => $this->click_through_rate,
      'data_date' => $this->data_date,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}
