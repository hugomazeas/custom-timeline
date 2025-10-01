<div class="min-h-screen p-8">

    <!-- Header Section -->
    <div class="max-w-7xl mx-auto mb-12">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-medium text-[#e2b714] mb-2">
                    timeline
                </h1>
                <p class="text-gray-500 text-sm">organize your events</p>
            </div>
            <button
                wire:click="openGroupModal"
                class="bg-[#2a2a2a] hover:bg-[#323232] text-gray-400 hover:text-[#e2b714] font-medium py-2.5 px-5 rounded-lg transition-colors duration-200 flex items-center space-x-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>new group</span>
            </button>
        </div>
    </div>

    <!-- Timeline Groups Container -->
    <div class="max-w-7xl mx-auto">
        <div class="space-y-8">
            @forelse ($groups as $group)
                <div class="bg-[#2a2a2a] rounded-lg overflow-hidden"
                     wire:key="{{ $group['id'] }}-group">
                    <div class="p-5 border-b border-gray-800">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-medium text-gray-300">{{ $group['name'] }}</h2>
                            <div class="flex items-center space-x-2">
                                <button
                                    wire:click="openRowModal('{{ $group['id'] }}')"
                                    class="bg-[#323232] hover:bg-[#3a3a3a] text-gray-400 hover:text-[#e2b714] font-medium py-2 px-3 rounded transition-colors duration-200 flex items-center space-x-1.5 text-sm"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>add row</span>
                                </button>
                                <button
                                    wire:click="deleteGroup('{{ $group['id'] }}')"
                                    wire:confirm="Are you sure you want to delete this timeline group?"
                                    class="bg-[#323232] hover:bg-[#3a3a3a] text-gray-400 hover:text-red-500 font-medium py-2 px-3 rounded transition-colors duration-200 text-sm"
                                >
                                    delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-12 gap-6">
                            <div class="col-span-3">
                                <h3 class="text-xs font-medium text-gray-500 mb-3 uppercase tracking-wider">rows</h3>
                                <div class="space-y-1.5">
                                    @foreach ($group['rows'] as $row)
                                        <div class="flex items-center justify-between p-2.5 bg-[#323232] rounded hover:bg-[#3a3a3a] transition-colors duration-200"
                                             wire:key="{{ $group['id'] }}-{{ $row['id'] }}-row">
                                            <span class="font-medium text-gray-400 text-sm">{{ $row['name'] }}</span>
                                            <div class="flex items-center space-x-1">
                                                <button
                                                    wire:click="openEventModal('{{ $group['id'] }}', '{{ $row['id'] }}')"
                                                    class="text-gray-500 hover:text-[#e2b714] p-1 rounded transition-colors duration-200"
                                                    title="Add Event"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                </button>
                                                <button
                                                    wire:click="deleteRow('{{ $group['id'] }}', '{{ $row['id'] }}')"
                                                    wire:confirm="Are you sure you want to delete this row?"
                                                    class="text-gray-500 hover:text-red-500 p-1 rounded transition-colors duration-200"
                                                    title="Delete Row"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-span-9">
                                <div
                                    id="timeline-{{ $group['id'] }}"
                                    class="h-96 bg-[#1a1a1a] rounded border border-gray-800"
                                    wire:key="{{ $group['id'] }}-timeline"
                                    data-timeline-group="{{ json_encode($group) }}"
                                    data-timeline-id="{{ $group['id'] }}"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="text-center py-24">
                    <div class="max-w-md mx-auto">
                        <svg class="w-16 h-16 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-500 mb-2">no timelines yet</h3>
                        <p class="text-gray-600 text-sm mb-6">create your first timeline group to get started</p>
                        <button
                            wire:click="openGroupModal"
                            class="bg-[#2a2a2a] hover:bg-[#323232] text-gray-400 hover:text-[#e2b714] font-medium py-2.5 px-6 rounded-lg transition-colors duration-200"
                        >
                            create timeline
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Group Modal -->
    @if ($showGroupModal)
        <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
            <div class="bg-[#2a2a2a] rounded-lg max-w-md w-full p-6 border border-gray-800">
                <h3 class="text-xl font-medium text-gray-300 mb-5">create timeline group</h3>
                <form wire:submit.prevent="createGroup({name: $wire.currentGroup.name})">
                    <div class="mb-5">
                        <label for="groupName" class="block text-sm font-medium text-gray-500 mb-2">group name</label>
                        <input
                            type="text"
                            id="groupName"
                            wire:model="currentGroup.name"
                            placeholder="project milestones"
                            class="w-full px-4 py-2.5 border border-gray-700 rounded bg-[#1a1a1a] focus:outline-none focus:border-[#e2b714] transition-colors duration-200 text-gray-300 placeholder-gray-600"
                            required
                        >
                    </div>
                    <div class="flex space-x-2">
                        <button
                            type="button"
                            wire:click="closeModals"
                            class="flex-1 bg-[#323232] hover:bg-[#3a3a3a] text-gray-400 font-medium py-2.5 px-4 rounded transition-colors duration-200"
                        >
                            cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 bg-[#e2b714] hover:bg-[#d4a913] text-black font-medium py-2.5 px-4 rounded transition-colors duration-200"
                        >
                            create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Event Creation Modal -->
    @if ($showEventModal)
        <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
            <div class="bg-[#2a2a2a] rounded-lg max-w-lg w-full p-6 border border-gray-800">
                <h3 class="text-xl font-medium text-gray-300 mb-5">add event</h3>
                <form wire:submit.prevent="createEvent({
                    title: $wire.currentEvent.title,
                    type: $wire.currentEvent.type,
                    start: $wire.currentEvent.start,
                    end: $wire.currentEvent.end,
                    color: $wire.currentEvent.color
                }, $wire.currentEvent.groupId, $wire.currentEvent.rowId)">

                    <div class="mb-4">
                        <label for="eventTitle" class="block text-sm font-medium text-gray-500 mb-2">event title</label>
                        <input
                            type="text"
                            id="eventTitle"
                            wire:model="currentEvent.title"
                            placeholder="product launch"
                            class="w-full px-4 py-2.5 border border-gray-700 rounded bg-[#1a1a1a] focus:outline-none focus:border-[#e2b714] transition-colors duration-200 text-gray-300 placeholder-gray-600"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-500 mb-2">event type</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" wire:model="currentEvent.type" value="punctual" class="text-[#e2b714] focus:ring-[#e2b714] bg-[#1a1a1a] border-gray-700">
                                <span class="ml-2 text-gray-400 text-sm">punctual</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" wire:model="currentEvent.type" value="timespan" class="text-[#e2b714] focus:ring-[#e2b714] bg-[#1a1a1a] border-gray-700">
                                <span class="ml-2 text-gray-400 text-sm">time span</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="eventStart" class="block text-sm font-medium text-gray-500 mb-2">start date</label>
                        <input
                            type="datetime-local"
                            id="eventStart"
                            wire:model="currentEvent.start"
                            class="w-full px-4 py-2.5 border border-gray-700 rounded bg-[#1a1a1a] focus:outline-none focus:border-[#e2b714] transition-colors duration-200 text-gray-300"
                            required
                        >
                    </div>

                    @if ($currentEvent['type'] === 'timespan')
                        <div class="mb-4">
                            <label for="eventEnd" class="block text-sm font-medium text-gray-500 mb-2">end date</label>
                            <input
                                type="datetime-local"
                                id="eventEnd"
                                wire:model="currentEvent.end"
                                class="w-full px-4 py-2.5 border border-gray-700 rounded bg-[#1a1a1a] focus:outline-none focus:border-[#e2b714] transition-colors duration-200 text-gray-300"
                            >
                        </div>
                    @endif

                    <div class="mb-5">
                        <label for="eventColor" class="block text-sm font-medium text-gray-500 mb-2">color</label>
                        <div class="flex space-x-2">
                            <input
                                type="color"
                                id="eventColor"
                                wire:model="currentEvent.color"
                                class="w-12 h-10 border border-gray-700 rounded bg-[#1a1a1a] cursor-pointer"
                            >
                            <input
                                type="text"
                                wire:model="currentEvent.color"
                                class="flex-1 px-4 py-2.5 border border-gray-700 rounded bg-[#1a1a1a] focus:outline-none focus:border-[#e2b714] transition-colors duration-200 text-gray-300"
                            >
                        </div>
                    </div>

                    <div class="flex space-x-2">
                        <button
                            type="button"
                            wire:click="closeModals"
                            class="flex-1 bg-[#323232] hover:bg-[#3a3a3a] text-gray-400 font-medium py-2.5 px-4 rounded transition-colors duration-200"
                        >
                            cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 bg-[#e2b714] hover:bg-[#d4a913] text-black font-medium py-2.5 px-4 rounded transition-colors duration-200"
                        >
                            add event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Row Creation Modal -->
    @if ($showRowModal)
        <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
            <div class="bg-[#2a2a2a] rounded-lg max-w-md w-full p-6 border border-gray-800">
                <h3 class="text-xl font-medium text-gray-300 mb-5">add row</h3>
                <form wire:submit.prevent="createRow({name: $wire.currentRow.name}, $wire.currentRow.groupId)">
                    <div class="mb-5">
                        <label for="rowName" class="block text-sm font-medium text-gray-500 mb-2">row name</label>
                        <input
                            type="text"
                            id="rowName"
                            wire:model="currentRow.name"
                            placeholder="development"
                            class="w-full px-4 py-2.5 border border-gray-700 rounded bg-[#1a1a1a] focus:outline-none focus:border-[#e2b714] transition-colors duration-200 text-gray-300 placeholder-gray-600"
                            required
                        >
                    </div>

                    <div class="flex space-x-2">
                        <button
                            type="button"
                            wire:click="closeModals"
                            class="flex-1 bg-[#323232] hover:bg-[#3a3a3a] text-gray-400 font-medium py-2.5 px-4 rounded transition-colors duration-200"
                        >
                            cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 bg-[#e2b714] hover:bg-[#d4a913] text-black font-medium py-2.5 px-4 rounded transition-colors duration-200"
                        >
                            add row
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>