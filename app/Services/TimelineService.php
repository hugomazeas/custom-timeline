<?php

namespace App\Services;

use App\Models\Event;
use App\Models\TimelineGroup;
use App\Models\TimelineRow;
use Illuminate\Database\Eloquent\Collection;

class TimelineService
{
    public function getAllGroups(): Collection
    {
        return TimelineGroup::with(['timelineRows.events'])->get();
    }

    public function createGroup(string $name): TimelineGroup
    {
        $group = TimelineGroup::create(['name' => $name]);

        $group->timelineRows()->create(['name' => 'Row 1']);

        return $group->load('timelineRows.events');
    }

    public function deleteGroup(int $groupId): bool
    {
        $group = TimelineGroup::find($groupId);

        if (! $group) {
            return false;
        }

        $group->delete();

        return true;
    }

    public function createRow(int $groupId, string $name): ?TimelineRow
    {
        $group = TimelineGroup::find($groupId);

        if (! $group) {
            return null;
        }

        return $group->timelineRows()->create(['name' => $name]);
    }

    public function deleteRow(int $rowId): bool
    {
        $row = TimelineRow::find($rowId);

        if (! $row) {
            return false;
        }

        $row->delete();

        return true;
    }

    public function moveRow(int $rowId, int $targetGroupId): ?TimelineRow
    {
        $row = TimelineRow::find($rowId);

        if (! $row) {
            return null;
        }

        $targetGroup = TimelineGroup::find($targetGroupId);

        if (! $targetGroup) {
            return null;
        }

        $row->update(['timeline_group_id' => $targetGroupId]);

        return $row->fresh();
    }

    public function createEvent(int $rowId, array $eventData): ?Event
    {
        $row = TimelineRow::find($rowId);

        if (! $row) {
            return null;
        }

        return $row->events()->create([
            'title' => $eventData['title'],
            'start_date' => $eventData['start'],
            'end_date' => $eventData['end'] ?? null,
            'color' => $eventData['color'],
            'is_deadline' => $eventData['is_deadline'] ?? false,
            'notes' => $eventData['notes'] ?? null,
            'links' => $eventData['links'] ?? null,
        ]);
    }

    public function updateEvent(int $eventId, array $eventData): ?Event
    {
        $event = Event::find($eventId);

        if (! $event) {
            return null;
        }

        $event->update([
            'title' => $eventData['title'],
            'start_date' => $eventData['start'],
            'end_date' => $eventData['end'] ?? null,
            'color' => $eventData['color'],
            'is_deadline' => $eventData['is_deadline'] ?? false,
            'notes' => $eventData['notes'] ?? null,
            'links' => $eventData['links'] ?? null,
        ]);

        return $event->fresh();
    }

    public function deleteEvent(int $eventId): bool
    {
        $event = Event::find($eventId);

        if (! $event) {
            return false;
        }

        $event->delete();

        return true;
    }
}
