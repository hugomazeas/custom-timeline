<?php

namespace App\Http\Controllers;

use App\Models\TimelineGroup;
use App\Models\TimelineRow;
use App\Models\Event;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function getGroups()
    {
        $groups = TimelineGroup::with(['timelineRows.events'])->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'created_at' => $group->created_at->toISOString(),
                'rows' => $group->timelineRows->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'name' => $row->name,
                        'events' => $row->events->map(function ($event) {
                            return [
                                'id' => $event->id,
                                'title' => $event->title,
                                'type' => $event->type,
                                'start' => $event->start_date->toISOString(),
                                'end' => $event->end_date?->toISOString(),
                                'color' => $event->color,
                                'created_at' => $event->created_at->toISOString()
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ];
        });

        return response()->json(['groups' => $formattedGroups]);
    }

    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $group = TimelineGroup::create([
            'name' => $request->input('name'),
        ]);

        $row = $group->timelineRows()->create([
            'name' => 'Row 1',
        ]);

        $formattedGroup = [
            'id' => $group->id,
            'name' => $group->name,
            'created_at' => $group->created_at->toISOString(),
            'rows' => [
                [
                    'id' => $row->id,
                    'name' => $row->name,
                    'events' => []
                ]
            ]
        ];

        return response()->json(['group' => $formattedGroup], 201);
    }

    public function deleteGroup(string $id)
    {
        $group = TimelineGroup::find($id);

        if (!$group) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        $group->delete();

        return response()->json(['success' => true]);
    }

    public function createRow(Request $request)
    {
        $request->validate([
            'group_id' => 'required|integer',
            'name' => 'required|string|max:255',
        ]);

        $group = TimelineGroup::find($request->input('group_id'));

        if (!$group) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        $row = $group->timelineRows()->create([
            'name' => $request->input('name'),
        ]);

        $formattedRow = [
            'id' => $row->id,
            'name' => $row->name,
            'events' => []
        ];

        return response()->json(['row' => $formattedRow], 201);
    }

    public function deleteRow(string $id)
    {
        $row = TimelineRow::find($id);

        if (!$row) {
            return response()->json(['error' => 'Row not found'], 404);
        }

        $row->delete();

        return response()->json(['success' => true]);
    }

    public function createEvent(Request $request)
    {
        $request->validate([
            'group_id' => 'required|integer',
            'row_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'color' => 'required|string'
        ]);

        $row = TimelineRow::find($request->input('row_id'));

        if (!$row) {
            return response()->json(['error' => 'Row not found'], 404);
        }

        $event = $row->events()->create([
            'title' => $request->input('title'),
            'start_date' => $request->input('start'),
            'end_date' => $request->input('end'),
            'color' => $request->input('color'),
        ]);

        $formattedEvent = [
            'id' => $event->id,
            'title' => $event->title,
            'type' => $event->type,
            'start' => $event->start_date->toISOString(),
            'end' => $event->end_date?->toISOString(),
            'color' => $event->color,
            'created_at' => $event->created_at->toISOString()
        ];

        return response()->json(['event' => $formattedEvent], 201);
    }

    public function deleteEvent(string $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $event->delete();

        return response()->json(['success' => true]);
    }
}
