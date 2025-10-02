<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = [
        'timeline_row_id',
        'title',
        'start_date',
        'end_date',
        'color',
        'is_deadline',
        'notes',
        'links',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_deadline' => 'boolean',
        ];
    }

    public function timelineRow(): BelongsTo
    {
        return $this->belongsTo(TimelineRow::class);
    }

    public function type(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->end_date ? 'timespan' : 'punctual'
        );
    }

    public function isPunctual(): bool
    {
        return $this->end_date === null;
    }

    public function isTimespan(): bool
    {
        return $this->end_date !== null;
    }
}
