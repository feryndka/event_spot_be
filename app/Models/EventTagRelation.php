<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventTagRelation extends Pivot
{
    protected $table = 'event_tag_relations';

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function tag()
    {
        return $this->belongsTo(EventTag::class, 'tag_id');
    }
}
