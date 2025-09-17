<div class="min-h-screen p-6">

    <!-- Header Section -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-white/20 p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 bg-clip-text text-transparent">
                        Beautiful Timeline
                    </h1>
                    <p class="text-slate-600 mt-2 text-lg">Create stunning visual timelines with ease</p>
                </div>
                <button
                    wire:click="openGroupModal"
                    class="group bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 flex items-center space-x-2"
                >
                    <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>New Timeline Group</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Timeline Groups Container -->
    <div class="max-w-7xl mx-auto">
        <div class="space-y-6">
            @forelse ($groups as $group)
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-white/20 overflow-hidden"
                     wire:key="{{ $group['id'] }}-group"
                     x-data="{ groupId: '{{ $group['id'] }}' }"
                     x-ref="group-{{ $group['id'] }}">
                    <div class="p-6 border-b border-slate-200/50">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-slate-800">{{ $group['name'] }}</h2>
                            <div class="flex items-center space-x-3">
                                <button
                                    wire:click="openRowModal('{{ $group['id'] }}')"
                                    class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-medium py-2 px-4 rounded-xl transition-all duration-200 flex items-center space-x-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>Add Row</span>
                                </button>
                                <button
                                    wire:click="deleteGroup('{{ $group['id'] }}')"
                                    wire:confirm="Are you sure you want to delete this timeline group?"
                                    class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2 px-4 rounded-xl transition-all duration-200"
                                >
                                    Delete Group
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-12 gap-4 mb-4">
                            <div class="col-span-3">
                                <h3 class="text-sm font-semibold text-slate-600 mb-3">Timeline Rows</h3>
                                <div class="space-y-2">
                                    @foreach ($group['rows'] as $row)
                                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl"
                                             wire:key="{{ $group['id'] }}-{{ $row['id'] }}-row">
                                            <span class="font-medium text-slate-700">{{ $row['name'] }}</span>
                                            <div class="flex items-center space-x-2">
                                                <button
                                                    wire:click="openEventModal('{{ $group['id'] }}', '{{ $row['id'] }}')"
                                                    class="text-indigo-600 hover:text-indigo-800 p-1 rounded-lg hover:bg-indigo-50 transition-all duration-200"
                                                    title="Add Event"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                </button>
                                                <button
                                                    wire:click="deleteRow('{{ $group['id'] }}', '{{ $row['id'] }}')"
                                                    wire:confirm="Are you sure you want to delete this row?"
                                                    class="text-red-600 hover:text-red-800 p-1 rounded-lg hover:bg-red-50 transition-all duration-200"
                                                    title="Delete Row"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    class="h-96 bg-white rounded-xl border border-slate-200"
                                    x-data="timelineWidget"
                                    x-init="$nextTick(() => initTimeline('{{ $group['id'] }}', {{ Js::from($group) }}))"
                                    wire:key="{{ $group['id'] }}-timeline-{{ json_encode($group['rows']) }}"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="bg-white/60 backdrop-blur-sm rounded-3xl shadow-lg border border-white/20 p-12 max-w-2xl mx-auto">
                        <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold text-slate-700 mb-3">Start Your Timeline Journey</h3>
                        <p class="text-slate-500 mb-6">Create your first timeline group to begin organizing your events and milestones</p>
                        <button
                            wire:click="openGroupModal"
                            class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold py-3 px-8 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300"
                        >
                            Create Your First Timeline
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Group Modal -->
    @if ($showGroupModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform transition-all duration-300 scale-100">
                <h3 class="text-2xl font-bold text-slate-800 mb-6">Create New Timeline Group</h3>
                <form wire:submit.prevent="createGroup({name: $wire.currentGroup.name})">
                    <div class="mb-6">
                        <label for="groupName" class="block text-sm font-semibold text-slate-700 mb-2">Group Name</label>
                        <input
                            type="text"
                            id="groupName"
                            wire:model="currentGroup.name"
                            placeholder="e.g., Project Milestones, Company History..."
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
                            required
                        >
                    </div>
                    <div class="flex space-x-3">
                        <button
                            type="button"
                            wire:click="closeModals"
                            class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-4 rounded-xl transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200"
                        >
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Event Creation Modal -->
    @if ($showEventModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 transform scale-100 transition-all duration-300">
                <h3 class="text-2xl font-bold text-slate-800 mb-6">Add New Event</h3>
                <form wire:submit.prevent="createEvent({
                    title: $wire.currentEvent.title,
                    type: $wire.currentEvent.type,
                    start: $wire.currentEvent.start,
                    end: $wire.currentEvent.end,
                    color: $wire.currentEvent.color
                }, $wire.currentEvent.groupId, $wire.currentEvent.rowId)">

                    <div class="mb-6">
                        <label for="eventTitle" class="block text-sm font-semibold text-slate-700 mb-2">Event Title</label>
                        <input
                            type="text"
                            id="eventTitle"
                            wire:model="currentEvent.title"
                            placeholder="e.g., Product Launch, Meeting..."
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
                            required
                        >
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-3">Event Type</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" wire:model="currentEvent.type" value="punctual" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-slate-700">Punctual Event</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" wire:model="currentEvent.type" value="timespan" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-slate-700">Time Span</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="eventStart" class="block text-sm font-semibold text-slate-700 mb-2">Start Date</label>
                        <input
                            type="datetime-local"
                            id="eventStart"
                            wire:model="currentEvent.start"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
                            required
                        >
                    </div>

                    @if ($currentEvent['type'] === 'timespan')
                        <div class="mb-6">
                            <label for="eventEnd" class="block text-sm font-semibold text-slate-700 mb-2">End Date</label>
                            <input
                                type="datetime-local"
                                id="eventEnd"
                                wire:model="currentEvent.end"
                                class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
                            >
                        </div>
                    @endif

                    <div class="mb-6">
                        <label for="eventColor" class="block text-sm font-semibold text-slate-700 mb-2">Color</label>
                        <div class="flex space-x-2">
                            <input
                                type="color"
                                id="eventColor"
                                wire:model="currentEvent.color"
                                class="w-12 h-12 border border-slate-200 rounded-xl cursor-pointer"
                            >
                            <input
                                type="text"
                                wire:model="currentEvent.color"
                                class="flex-1 px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
                            >
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <button
                            type="button"
                            wire:click="closeModals"
                            class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-4 rounded-xl transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200"
                        >
                            Add Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Row Creation Modal -->
    @if ($showRowModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform scale-100 transition-all duration-300">
                <h3 class="text-2xl font-bold text-slate-800 mb-6">Add New Row</h3>
                <form wire:submit.prevent="createRow({name: $wire.currentRow.name}, $wire.currentRow.groupId)">
                    <div class="mb-6">
                        <label for="rowName" class="block text-sm font-semibold text-slate-700 mb-2">Row Name</label>
                        <input
                            type="text"
                            id="rowName"
                            wire:model="currentRow.name"
                            placeholder="e.g., Development, Marketing, Sales..."
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
                            required
                        >
                    </div>

                    <div class="flex space-x-3">
                        <button
                            type="button"
                            wire:click="closeModals"
                            class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-4 rounded-xl transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200"
                        >
                            Add Row
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>