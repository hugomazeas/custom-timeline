<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\CreateTimelineGroupRequest;
use App\Http\Requests\CreateTimelineRowRequest;
use App\Http\Requests\MoveRowRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Http\Resources\TimelineGroupResource;
use App\Services\TimelineService;

class TimelineController extends Controller
{
    public function __construct(
        private readonly TimelineService $timelineService
    ) {}

    public function getGroups()
    {
        $groups = $this->timelineService->getAllGroups();

        return response()->json([
            'groups' => TimelineGroupResource::collection($groups),
        ]);
    }

    public function createGroup(CreateTimelineGroupRequest $request)
    {
        $group = $this->timelineService->createGroup($request->validated('name'));

        return response()->json([
            'group' => new TimelineGroupResource($group),
        ], 201);
    }

    public function deleteGroup(string $id)
    {
        $this->timelineService->deleteGroup((int) $id);

        return response()->json(['success' => true]);
    }

    public function createRow(CreateTimelineRowRequest $request)
    {
        $validated = $request->validated();

        $row = $this->timelineService->createRow(
            $validated['group_id'],
            $validated['name']
        );

        return response()->json([
            'row' => [
                'id' => $row->id,
                'name' => $row->name,
                'events' => [],
            ],
        ], 201);
    }

    public function deleteRow(string $id)
    {
        $this->timelineService->deleteRow((int) $id);

        return response()->json(['success' => true]);
    }

    public function moveRow(MoveRowRequest $request, string $id)
    {
        $validated = $request->validated();

        $this->timelineService->moveRow(
            (int) $id,
            $validated['target_group_id']
        );

        return response()->json(['success' => true]);
    }

    public function createEvent(CreateEventRequest $request)
    {
        $validated = $request->validated();

        $event = $this->timelineService->createEvent(
            $validated['row_id'],
            $validated
        );

        return response()->json([
            'event' => new EventResource($event),
        ], 201);
    }

    public function updateEvent(UpdateEventRequest $request, string $id)
    {
        $validated = $request->validated();

        $event = $this->timelineService->updateEvent((int) $id, $validated);

        return response()->json([
            'event' => new EventResource($event),
        ]);
    }

    public function deleteEvent(string $id)
    {
        $this->timelineService->deleteEvent((int) $id);

        return response()->json(['success' => true]);
    }
}
