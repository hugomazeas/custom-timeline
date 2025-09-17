@extends('layouts.app')

@section('content')
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
                    id="createGroupBtn"
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
        <div id="timelineGroups" class="space-y-6">
            <!-- Timeline groups will be dynamically inserted here -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="text-center py-16">
            <div class="bg-white/60 backdrop-blur-sm rounded-3xl shadow-lg border border-white/20 p-12 max-w-2xl mx-auto">
                <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-slate-700 mb-3">Start Your Timeline Journey</h3>
                <p class="text-slate-500 mb-6">Create your first timeline group to begin organizing your events and milestones</p>
                <button
                    onclick="document.getElementById('createGroupBtn').click()"
                    class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold py-3 px-8 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300"
                >
                    Create Your First Timeline
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Group Modal -->
<div id="createGroupModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform scale-95 transition-all duration-300">
        <h3 class="text-2xl font-bold text-slate-800 mb-6">Create New Timeline Group</h3>
        <form id="createGroupForm">
            <div class="mb-6">
                <label for="groupName" class="block text-sm font-semibold text-slate-700 mb-2">Group Name</label>
                <input
                    type="text"
                    id="groupName"
                    name="groupName"
                    placeholder="e.g., Project Milestones, Company History..."
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
                    required
                >
            </div>
            <div class="flex space-x-3">
                <button
                    type="button"
                    id="cancelGroupBtn"
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

<!-- Event Creation Modal -->
<div id="eventModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 transform scale-95 transition-all duration-300">
        <h3 class="text-2xl font-bold text-slate-800 mb-6">Add New Event</h3>
        <form id="eventForm">
            <input type="hidden" id="eventGroupId">
            <input type="hidden" id="eventRowId">

            <div class="mb-6">
                <label for="eventTitle" class="block text-sm font-semibold text-slate-700 mb-2">Event Title</label>
                <input
                    type="text"
                    id="eventTitle"
                    name="eventTitle"
                    placeholder="e.g., Product Launch, Meeting..."
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
                    required
                >
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-3">Event Type</label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="eventType" value="punctual" class="text-indigo-600 focus:ring-indigo-500" checked>
                        <span class="ml-2 text-slate-700">Punctual Event</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="eventType" value="timespan" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-slate-700">Time Span</span>
                    </label>
                </div>
            </div>

            <div class="mb-6">
                <label for="eventStart" class="block text-sm font-semibold text-slate-700 mb-2">Start Date</label>
                <input
                    type="datetime-local"
                    id="eventStart"
                    name="eventStart"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
                    required
                >
            </div>

            <div id="endDateContainer" class="mb-6 hidden">
                <label for="eventEnd" class="block text-sm font-semibold text-slate-700 mb-2">End Date</label>
                <input
                    type="datetime-local"
                    id="eventEnd"
                    name="eventEnd"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
                >
            </div>

            <div class="mb-6">
                <label for="eventColor" class="block text-sm font-semibold text-slate-700 mb-2">Color</label>
                <div class="flex space-x-2">
                    <input type="color" id="eventColor" name="eventColor" value="#6366f1" class="w-12 h-12 border border-slate-200 rounded-xl cursor-pointer">
                    <input
                        type="text"
                        id="eventColorHex"
                        value="#6366f1"
                        class="flex-1 px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
                    >
                </div>
            </div>

            <div class="flex space-x-3">
                <button
                    type="button"
                    id="cancelEventBtn"
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

<!-- Row Creation Modal -->
<div id="rowModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform scale-95 transition-all duration-300">
        <h3 class="text-2xl font-bold text-slate-800 mb-6">Add New Row</h3>
        <form id="rowForm">
            <input type="hidden" id="rowGroupId">

            <div class="mb-6">
                <label for="rowName" class="block text-sm font-semibold text-slate-700 mb-2">Row Name</label>
                <input
                    type="text"
                    id="rowName"
                    name="rowName"
                    placeholder="e.g., Development, Marketing, Sales..."
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
                    required
                >
            </div>

            <div class="flex space-x-3">
                <button
                    type="button"
                    id="cancelRowBtn"
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
@endsection