<?php

namespace App\Livewire;

use App\Models\TimelineGroup;
use App\Models\TimelineRow;
use App\Models\Event;
use Livewire\Component;
use Livewire\Attributes\On;

class Timeline extends Component
{
    public array $groups = [];
    public array $currentEvent = [];
    public array $currentGroup = [];
    public array $currentRow = [];

    public bool $showGroupModal = false;
    public bool $showEventModal = false;
    public bool $showRowModal = false;

    public function mount()
    {
        $this->loadData();
    }

    public function createGroup(array $data)
    {
        $group = TimelineGroup::create([
            'name' => $data['name'],
        ]);

        $row = $group->timelineRows()->create([
            'name' => 'Row 1',
        ]);

        $this->loadData();
        $this->showGroupModal = false;
        $this->dispatch('group-created', $group->id);
    }

    public function createRow(array $data, int $groupId)
    {
        $group = TimelineGroup::find($groupId);
        if (!$group) return;

        $group->timelineRows()->create([
            'name' => $data['name'],
        ]);

        $this->loadData();
        $this->showRowModal = false;
        $this->dispatch('timeline-updated', ['groupId' => $groupId]);
    }

    public function createEvent(array $data, int $groupId, int $rowId)
    {
        $row = TimelineRow::find($rowId);
        if (!$row) return;

        $row->events()->create([
            'title' => $data['title'],
            'start_date' => $data['start'],
            'end_date' => $data['end'] ?? null,
            'color' => $data['color'] ?? '#6366f1',
        ]);

        $this->loadData();
        $this->showEventModal = false;
        $this->dispatch('timeline-updated', ['groupId' => $groupId]);
    }

    public function deleteGroup(int $groupId)
    {
        $group = TimelineGroup::find($groupId);
        if ($group) {
            $group->delete();
        }

        $this->loadData();
        $this->dispatch('group-deleted', $groupId);
    }

    public function deleteRow(int $groupId, int $rowId)
    {
        $row = TimelineRow::find($rowId);
        if ($row) {
            $row->delete();
        }

        $this->loadData();
        $this->dispatch('timeline-updated', ['groupId' => $groupId]);
    }

    public function openGroupModal()
    {
        $this->currentGroup = [];
        $this->showGroupModal = true;
    }

    public function openEventModal(int $groupId, int $rowId)
    {
        $this->currentEvent = [
            'groupId' => $groupId,
            'rowId' => $rowId,
            'type' => 'punctual',
            'color' => '#6366f1'
        ];
        $this->showEventModal = true;
    }

    public function openRowModal(int $groupId)
    {
        $this->currentRow = ['groupId' => $groupId];
        $this->showRowModal = true;
    }

    public function closeModals()
    {
        $this->showGroupModal = false;
        $this->showEventModal = false;
        $this->showRowModal = false;
        $this->currentEvent = [];
        $this->currentGroup = [];
        $this->currentRow = [];
    }

    private function loadData(): void
    {
        $groups = TimelineGroup::with(['timelineRows.events'])->get();

        $this->groups = $groups->map(function ($group) {
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
        })->toArray();
    }

    public function render()
    {
        return view('livewire.timeline')->layout('layouts.app');
    }
}
