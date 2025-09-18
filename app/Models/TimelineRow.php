<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimelineRow extends Model
{
    protected $fillable = [
        'timeline_group_id',
        'name',
    ];

    public function timelineGroup(): BelongsTo
    {
        return $this->belongsTo(TimelineGroup::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
