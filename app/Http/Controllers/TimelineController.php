<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TimelineController extends Controller
{
    // Simple in-memory storage for demo purposes
    private function getStorageFile(): string
    {
        return storage_path('app/timeline_data.json');
    }

    private function loadData(): array
    {
        $file = $this->getStorageFile();
        if (!file_exists($file)) {
            return ['groups' => []];
        }

        $data = json_decode(file_get_contents($file), true);
        return $data ?: ['groups' => []];
    }

    private function saveData(array $data): void
    {
        $file = $this->getStorageFile();
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function getGroups()
    {
        $data = $this->loadData();
        return response()->json(['groups' => $data['groups']]);
    }

    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = $this->loadData();

        $group = [
            'id' => 'id_' . Str::random(10) . '_' . time(),
            'name' => $request->input('name'),
            'rows' => [
                [
                    'id' => 'id_' . Str::random(10) . '_' . time(),
                    'name' => 'Row 1',
                    'events' => []
                ]
            ],
            'created_at' => now()->toISOString()
        ];

        $data['groups'][] = $group;
        $this->saveData($data);

        return response()->json(['group' => $group], 201);
    }

    public function deleteGroup(string $id)
    {
        $data = $this->loadData();
        $data['groups'] = array_filter($data['groups'], fn($g) => $g['id'] !== $id);
        $data['groups'] = array_values($data['groups']); // Re-index array
        $this->saveData($data);

        return response()->json(['success' => true]);
    }

    public function createRow(Request $request)
    {
        $request->validate([
            'group_id' => 'required|string',
            'name' => 'required|string|max:255',
        ]);

        $data = $this->loadData();
        $groupIndex = null;

        foreach ($data['groups'] as $index => $group) {
            if ($group['id'] === $request->input('group_id')) {
                $groupIndex = $index;
                break;
            }
        }

        if ($groupIndex === null) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        $row = [
            'id' => 'id_' . Str::random(10) . '_' . time(),
            'name' => $request->input('name'),
            'events' => []
        ];

        $data['groups'][$groupIndex]['rows'][] = $row;
        $this->saveData($data);

        return response()->json(['row' => $row], 201);
    }

    public function deleteRow(string $id)
    {
        $data = $this->loadData();

        foreach ($data['groups'] as $groupIndex => $group) {
            foreach ($group['rows'] as $rowIndex => $row) {
                if ($row['id'] === $id) {
                    unset($data['groups'][$groupIndex]['rows'][$rowIndex]);
                    $data['groups'][$groupIndex]['rows'] = array_values($data['groups'][$groupIndex]['rows']);
                    $this->saveData($data);
                    return response()->json(['success' => true]);
                }
            }
        }

        return response()->json(['error' => 'Row not found'], 404);
    }

    public function createEvent(Request $request)
    {
        $request->validate([
            'group_id' => 'required|string',
            'row_id' => 'required|string',
            'title' => 'required|string|max:255',
            'type' => 'required|in:punctual,timespan',
            'start' => 'required|date',
            'end' => 'nullable|date|after:start',
            'color' => 'required|string'
        ]);

        $data = $this->loadData();
        $groupIndex = null;
        $rowIndex = null;

        foreach ($data['groups'] as $gIndex => $group) {
            if ($group['id'] === $request->input('group_id')) {
                $groupIndex = $gIndex;
                foreach ($group['rows'] as $rIndex => $row) {
                    if ($row['id'] === $request->input('row_id')) {
                        $rowIndex = $rIndex;
                        break 2;
                    }
                }
            }
        }

        if ($groupIndex === null || $rowIndex === null) {
            return response()->json(['error' => 'Group or row not found'], 404);
        }

        $event = [
            'id' => 'id_' . Str::random(10) . '_' . time(),
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            'start' => $request->input('start'),
            'end' => $request->input('end'),
            'color' => $request->input('color'),
            'created_at' => now()->toISOString()
        ];

        $data['groups'][$groupIndex]['rows'][$rowIndex]['events'][] = $event;
        $this->saveData($data);

        return response()->json(['event' => $event], 201);
    }

    public function deleteEvent(string $id)
    {
        $data = $this->loadData();

        foreach ($data['groups'] as $groupIndex => $group) {
            foreach ($group['rows'] as $rowIndex => $row) {
                foreach ($row['events'] as $eventIndex => $event) {
                    if ($event['id'] === $id) {
                        unset($data['groups'][$groupIndex]['rows'][$rowIndex]['events'][$eventIndex]);
                        $data['groups'][$groupIndex]['rows'][$rowIndex]['events'] = array_values($data['groups'][$groupIndex]['rows'][$rowIndex]['events']);
                        $this->saveData($data);
                        return response()->json(['success' => true]);
                    }
                }
            }
        }

        return response()->json(['error' => 'Event not found'], 404);
    }
}