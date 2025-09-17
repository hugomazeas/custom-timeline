<?php

namespace App\Livewire;

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
        $group = [
            'id' => $this->generateId(),
            'name' => $data['name'],
            'rows' => [
                [
                    'id' => $this->generateId(),
                    'name' => 'Row 1',
                    'events' => []
                ]
            ],
            'created_at' => now()->toISOString()
        ];

        $this->groups[] = $group;
        $this->saveData();
        $this->showGroupModal = false;
        $this->dispatch('group-created', $group['id']);
    }

    public function createRow(array $data, string $groupId)
    {
        $groupIndex = $this->findGroupIndex($groupId);
        if ($groupIndex === null) return;

        $newRow = [
            'id' => $this->generateId(),
            'name' => $data['name'],
            'events' => []
        ];

        $this->groups[$groupIndex]['rows'][] = $newRow;
        $this->saveData();
        $this->showRowModal = false;
        $this->dispatch('timeline-updated', ['groupId' => $groupId]);
    }

    public function createEvent(array $data, string $groupId, string $rowId)
    {
        $groupIndex = $this->findGroupIndex($groupId);
        if ($groupIndex === null) return;

        $rowIndex = $this->findRowIndex($groupIndex, $rowId);
        if ($rowIndex === null) return;

        $event = [
            'id' => $this->generateId(),
            'title' => $data['title'],
            'type' => $data['type'],
            'start' => $data['start'],
            'end' => $data['end'] ?? null,
            'color' => $data['color'] ?? '#6366f1'
        ];

        $this->groups[$groupIndex]['rows'][$rowIndex]['events'][] = $event;
        $this->saveData();
        $this->showEventModal = false;
        $this->dispatch('timeline-updated', ['groupId' => $groupId]);
    }

    public function deleteGroup(string $groupId)
    {
        $this->groups = array_values(array_filter($this->groups, fn($g) => $g['id'] !== $groupId));
        $this->saveData();
        $this->dispatch('group-deleted', $groupId);
    }

    public function deleteRow(string $groupId, string $rowId)
    {
        $groupIndex = $this->findGroupIndex($groupId);
        if ($groupIndex === null) return;

        $this->groups[$groupIndex]['rows'] = array_values(
            array_filter($this->groups[$groupIndex]['rows'], fn($r) => $r['id'] !== $rowId)
        );
        $this->saveData();
        $this->dispatch('timeline-updated', ['groupId' => $groupId]);
    }

    public function openGroupModal()
    {
        $this->currentGroup = [];
        $this->showGroupModal = true;
    }

    public function openEventModal(string $groupId, string $rowId)
    {
        $this->currentEvent = [
            'groupId' => $groupId,
            'rowId' => $rowId,
            'type' => 'punctual',
            'color' => '#6366f1'
        ];
        $this->showEventModal = true;
    }

    public function openRowModal(string $groupId)
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

    private function findGroupIndex(string $groupId): ?int
    {
        foreach ($this->groups as $index => $group) {
            if ($group['id'] === $groupId) {
                return $index;
            }
        }
        return null;
    }

    private function findRowIndex(int $groupIndex, string $rowId): ?int
    {
        foreach ($this->groups[$groupIndex]['rows'] as $index => $row) {
            if ($row['id'] === $rowId) {
                return $index;
            }
        }
        return null;
    }

    private function generateId(): string
    {
        return 'id_' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 9) . '_' . time();
    }

    private function saveData(): void
    {
        session(['timeline_groups' => $this->groups]);
    }

    private function loadData(): void
    {
        $this->groups = session('timeline_groups', []);
    }

    public function render()
    {
        return view('livewire.timeline')->layout('layouts.app');
    }
}
