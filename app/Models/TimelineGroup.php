<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimelineGroup extends Model
{
    protected $fillable = [
        'name',
    ];

    public function timelineRows(): HasMany
    {
        return $this->hasMany(TimelineRow::class);
    }
}
