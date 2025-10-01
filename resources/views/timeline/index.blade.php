@extends('layouts.app')

@section('content')
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
                id="createGroupBtn"
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
        <div id="timelineGroups" class="space-y-8">
            <!-- Timeline groups will be dynamically inserted here -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="text-center py-24">
            <div class="max-w-md mx-auto">
                <svg class="w-16 h-16 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-500 mb-2">no timelines yet</h3>
                <p class="text-gray-600 text-sm mb-6">create your first timeline group to get started</p>
                <button
                    onclick="document.getElementById('createGroupBtn').click()"
                    class="bg-[#2a2a2a] hover:bg-[#323232] text-gray-400 hover:text-[#e2b714] font-medium py-2.5 px-6 rounded-lg transition-colors duration-200"
                >
                    create timeline
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Group Modal -->
<div id="createGroupModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 p-4">
    <div class="bg-[#2a2a2a] rounded-lg max-w-md w-full p-6 border border-gray-800">
        <h3 class="text-xl font-medium text-gray-300 mb-5">create timeline group</h3>
        <form id="createGroupForm">
            <div class="mb-5">
                <label for="groupName" class="block text-sm font-medium text-gray-500 mb-2">group name</label>
                <input
                    type="text"
                    id="groupName"
                    name="groupName"
                    placeholder="project milestones"
                    class="w-full px-4 py-2.5 border border-gray-700 rounded bg-[#1a1a1a] focus:outline-none focus:border-[#e2b714] transition-colors duration-200 text-gray-300 placeholder-gray-600"
                    required
                >
            </div>
            <div class="flex space-x-2">
                <button
                    type="button"
                    id="cancelGroupBtn"
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

<!-- Event Creation Modal -->
<div id="eventModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 p-4">
    <div class="bg-[#2a2a2a] rounded-lg max-w-lg w-full p-6 border border-gray-800">
        <h3 class="text-xl font-medium text-gray-300 mb-5">add event</h3>
        <form id="eventForm">
            <input type="hidden" id="eventGroupId">
            <input type="hidden" id="eventRowId">

            <div class="mb-4">
                <label for="eventTitle" class="block text-sm font-medium text-gray-500 mb-2">event title</label>
                <input
                    type="text"
                    id="eventTitle"
                    name="eventTitle"
                    placeholder="product launch"
                    class="w-full px-4 py-2.5 border border-gray-700 rounded bg-[#1a1a1a] focus:outline-none focus:border-[#e2b714] transition-colors duration-200 text-gray-300 placeholder-gray-600"
                    required
                >
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-500 mb-2">event type</label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="eventType" value="punctual" class="text-[#e2b714] focus:ring-[#e2b714] bg-[#1a1a1a] border-gray-700" checked>
                        <span class="ml-2 text-gray-400 text-sm">punctual</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="eventType" value="timespan" class="text-[#e2b714] focus:ring-[#e2b714] bg-[#1a1a1a] border-gray-700">
                        <span class="ml-2 text-gray-400 text-sm">time span</span>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label for="eventStart" class="block text-sm font-medium text-gray-500 mb-2">start date</label>
                <input
                    type="datetime-local"
                    id="eventStart"
                    name="eventStart"
                    class="w-full px-4 py-2.5 border border-gray-700 rounded bg-[#1a1a1a] focus:outline-none focus:border-[#e2b714] transition-colors duration-200 text-gray-300"
                    required
                >
            </div>

            <div id="endDateContainer" class="mb-4 hidden">
                <label for="eventEnd" class="block text-sm font-medium text-gray-500 mb-2">end date</label>
                <input
                    type="datetime-local"
                    id="eventEnd"
                    name="eventEnd"
                    class="w-full px-4 py-2.5 border border-gray-700 rounded bg-[#1a1a1a] focus:outline-none focus:border-[#e2b714] transition-colors duration-200 text-gray-300"
                >
            </div>

            <div class="mb-5">
                <label for="eventColor" class="block text-sm font-medium text-gray-500 mb-2">color</label>
                <div class="flex space-x-2">
                    <input type="color" id="eventColor" name="eventColor" value="#6366f1" class="w-12 h-10 border border-gray-700 rounded bg-[#1a1a1a] cursor-pointer">
                    <input
                        type="text"
                        id="eventColorHex"
                        value="#6366f1"
                        class="flex-1 px-4 py-2.5 border border-gray-700 rounded bg-[#1a1a1a] focus:outline-none focus:border-[#e2b714] transition-colors duration-200 text-gray-300"
                    >
                </div>
            </div>

            <div class="flex space-x-2">
                <button
                    type="button"
                    id="cancelEventBtn"
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

<!-- Row Creation Modal -->
<div id="rowModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 p-4">
    <div class="bg-[#2a2a2a] rounded-lg max-w-md w-full p-6 border border-gray-800">
        <h3 class="text-xl font-medium text-gray-300 mb-5">add row</h3>
        <form id="rowForm">
            <input type="hidden" id="rowGroupId">

            <div class="mb-5">
                <label for="rowName" class="block text-sm font-medium text-gray-500 mb-2">row name</label>
                <input
                    type="text"
                    id="rowName"
                    name="rowName"
                    placeholder="development"
                    class="w-full px-4 py-2.5 border border-gray-700 rounded bg-[#1a1a1a] focus:outline-none focus:border-[#e2b714] transition-colors duration-200 text-gray-300 placeholder-gray-600"
                    required
                >
            </div>

            <div class="flex space-x-2">
                <button
                    type="button"
                    id="cancelRowBtn"
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
@endsection