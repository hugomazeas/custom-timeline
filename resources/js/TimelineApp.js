import { Timeline } from 'vis-timeline/standalone';

export class TimelineApp {
    constructor() {
        this.groups = [];
        this.timelines = new Map();
        this.init();
    }

    init() {
        this.bindEventListeners();
        this.loadGroups();
    }

    bindEventListeners() {
        // Helper function to safely add event listeners
        const addListener = (id, event, handler) => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener(event, handler);
            } else {
                console.warn(`Element with id '${id}' not found`);
            }
        };

        // Create group modal
        addListener('createGroupBtn', 'click', () => this.showGroupModal());
        addListener('cancelGroupBtn', 'click', () => this.hideGroupModal());
        addListener('createGroupForm', 'submit', (e) => {
            e.preventDefault();
            this.createGroup();
        });

        // Event modal
        addListener('cancelEventBtn', 'click', () => this.hideEventModal());
        addListener('eventForm', 'submit', (e) => {
            e.preventDefault();
            this.createEvent();
        });

        // Row modal
        addListener('cancelRowBtn', 'click', () => this.hideRowModal());
        addListener('rowForm', 'submit', (e) => {
            e.preventDefault();
            this.createRow();
        });

        // Event type radio buttons
        const eventTypeRadios = document.querySelectorAll('input[name="eventType"]');
        eventTypeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                this.toggleEndDateField();
            });
        });

        // Color picker sync
        const colorPicker = document.getElementById('eventColor');
        const colorHex = document.getElementById('eventColorHex');

        if (colorPicker && colorHex) {
            colorPicker.addEventListener('input', () => {
                colorHex.value = colorPicker.value;
            });

            colorHex.addEventListener('input', () => {
                colorPicker.value = colorHex.value;
            });
        }
    }

    async loadGroups() {
        try {
            const response = await fetch('/api/timeline-groups');
            const data = await response.json();
            this.groups = data.groups || [];
            this.renderGroups();
        } catch (error) {
            console.error('Failed to load groups:', error);
            this.groups = [];
            this.renderGroups();
        }
    }

    renderGroups() {
        const container = document.getElementById('timelineGroups');
        const emptyState = document.getElementById('emptyState');

        if (this.groups.length === 0) {
            container.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        container.innerHTML = '';

        this.groups.forEach(group => {
            const groupElement = this.createGroupElement(group);
            container.appendChild(groupElement);
            this.initializeTimeline(group.id, group);
        });
    }

    createGroupElement(group) {
        const div = document.createElement('div');
        div.className = 'bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-white/20 overflow-hidden';
        div.innerHTML = `
            <div class="p-6 border-b border-slate-200/50">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-slate-800">${group.name}</h2>
                    <div class="flex items-center space-x-3">
                        <button onclick="timelineApp.showRowModal('${group.id}')" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-medium py-2 px-4 rounded-xl transition-all duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Add Row</span>
                        </button>
                        <button onclick="timelineApp.deleteGroup('${group.id}')" class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2 px-4 rounded-xl transition-all duration-200">
                            Delete Group
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-12 gap-4 mb-4">
                    <div class="col-span-3">
                        <h3 class="text-sm font-semibold text-slate-600 mb-3">Timeline Rows</h3>
                        <div class="space-y-2" id="rows-${group.id}">
                            ${group.rows.map(row => `
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                                    <span class="font-medium text-slate-700">${row.name}</span>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="timelineApp.showEventModal('${group.id}', '${row.id}')" class="text-indigo-600 hover:text-indigo-800 p-1 rounded-lg hover:bg-indigo-50 transition-all duration-200" title="Add Event">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                        <button onclick="timelineApp.deleteRow('${group.id}', '${row.id}')" class="text-red-600 hover:text-red-800 p-1 rounded-lg hover:bg-red-50 transition-all duration-200" title="Delete Row">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="col-span-9">
                        <div id="timeline-${group.id}" class="h-96 bg-white rounded-xl border border-slate-200"></div>
                    </div>
                </div>
            </div>
        `;
        return div;
    }

    initializeTimeline(groupId, groupData) {
        const container = document.getElementById(`timeline-${groupId}`);
        if (!container) {
            console.error('Timeline container not found for group:', groupId);
            return;
        }

        // Prepare vis-timeline data
        const items = [];
        const groups = [];

        groupData.rows.forEach(row => {
            groups.push({
                id: row.id,
                content: row.name
            });

            row.events.forEach(event => {
                const item = {
                    id: event.id,
                    content: event.title,
                    start: new Date(event.start),
                    group: row.id,
                    style: `background-color: ${event.color}; border-color: ${event.color};`
                };

                if (event.type === 'timespan' && event.end) {
                    item.end = new Date(event.end);
                    item.type = 'range';
                } else {
                    item.type = 'point';
                }

                items.push(item);
            });
        });

        // Timeline options
        const options = {
            height: '100%',
            stack: true,
            orientation: 'top',
            showCurrentTime: true,
            zoomMin: 1000 * 60 * 60 * 24, // one day
            zoomMax: 1000 * 60 * 60 * 24 * 365 * 10, // ten years
            editable: false,
            selectable: true,
            margin: {
                item: 10,
                axis: 20
            }
        };

        // Create timeline
        try {
            const timeline = new Timeline(container, items, groups, options);
            this.timelines.set(groupId, timeline);
            console.log('Timeline created for group:', groupId);
        } catch (error) {
            console.error('Error creating timeline:', error);
        }
    }

    updateTimeline(groupId, groupData) {
        const timeline = this.timelines.get(groupId);
        if (!timeline) {
            console.warn('Timeline not found for group:', groupId);
            return;
        }

        // Prepare vis-timeline data
        const items = [];
        const groups = [];

        groupData.rows.forEach(row => {
            groups.push({
                id: row.id,
                content: row.name
            });

            row.events.forEach(event => {
                const item = {
                    id: event.id,
                    content: event.title,
                    start: new Date(event.start),
                    group: row.id,
                    style: `background-color: ${event.color}; border-color: ${event.color};`
                };

                if (event.type === 'timespan' && event.end) {
                    item.end = new Date(event.end);
                    item.type = 'range';
                } else {
                    item.type = 'point';
                }

                items.push(item);
            });
        });

        // Update timeline
        timeline.setItems(items);
        timeline.setGroups(groups);
        console.log('Timeline updated for group:', groupId);
    }

    // Modal management
    showGroupModal() {
        document.getElementById('createGroupModal').classList.remove('hidden');
        document.getElementById('createGroupModal').classList.add('flex');
        document.getElementById('groupName').value = '';
        document.getElementById('groupName').focus();
    }

    hideGroupModal() {
        document.getElementById('createGroupModal').classList.add('hidden');
        document.getElementById('createGroupModal').classList.remove('flex');
    }

    showEventModal(groupId, rowId) {
        document.getElementById('eventModal').classList.remove('hidden');
        document.getElementById('eventModal').classList.add('flex');
        document.getElementById('eventGroupId').value = groupId;
        document.getElementById('eventRowId').value = rowId;

        // Reset form
        document.getElementById('eventTitle').value = '';
        document.getElementById('eventStart').value = '';
        document.getElementById('eventEnd').value = '';
        document.getElementById('eventColor').value = '#6366f1';
        document.getElementById('eventColorHex').value = '#6366f1';
        document.querySelector('input[name="eventType"][value="punctual"]').checked = true;
        this.toggleEndDateField();

        document.getElementById('eventTitle').focus();
    }

    hideEventModal() {
        document.getElementById('eventModal').classList.add('hidden');
        document.getElementById('eventModal').classList.remove('flex');
    }

    showRowModal(groupId) {
        document.getElementById('rowModal').classList.remove('hidden');
        document.getElementById('rowModal').classList.add('flex');
        document.getElementById('rowGroupId').value = groupId;
        document.getElementById('rowName').value = '';
        document.getElementById('rowName').focus();
    }

    hideRowModal() {
        document.getElementById('rowModal').classList.add('hidden');
        document.getElementById('rowModal').classList.remove('flex');
    }

    toggleEndDateField() {
        const eventType = document.querySelector('input[name="eventType"]:checked').value;
        const endDateContainer = document.getElementById('endDateContainer');

        if (eventType === 'timespan') {
            endDateContainer.classList.remove('hidden');
        } else {
            endDateContainer.classList.add('hidden');
        }
    }

    // API calls
    async createGroup() {
        const name = document.getElementById('groupName').value.trim();
        if (!name) return;

        try {
            const response = await fetch('/api/timeline-groups', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ name })
            });

            if (response.ok) {
                const data = await response.json();
                this.groups.push(data.group);
                this.renderGroups();
                this.hideGroupModal();
            } else {
                console.error('Failed to create group');
            }
        } catch (error) {
            console.error('Error creating group:', error);
        }
    }

    async createEvent() {
        const groupId = document.getElementById('eventGroupId').value;
        const rowId = document.getElementById('eventRowId').value;
        const title = document.getElementById('eventTitle').value.trim();
        const type = document.querySelector('input[name="eventType"]:checked').value;
        const start = document.getElementById('eventStart').value;
        const end = document.getElementById('eventEnd').value;
        const color = document.getElementById('eventColor').value;

        if (!title || !start) return;

        try {
            const response = await fetch('/api/timeline-events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    group_id: groupId,
                    row_id: rowId,
                    title,
                    type,
                    start,
                    end: type === 'timespan' ? end : null,
                    color
                })
            });

            if (response.ok) {
                const data = await response.json();
                // Update local data
                const group = this.groups.find(g => g.id === groupId);
                if (group) {
                    const row = group.rows.find(r => r.id === rowId);
                    if (row) {
                        row.events.push(data.event);
                        this.updateTimeline(groupId, group);
                    }
                }
                this.hideEventModal();
            } else {
                console.error('Failed to create event');
            }
        } catch (error) {
            console.error('Error creating event:', error);
        }
    }

    async createRow() {
        const groupId = document.getElementById('rowGroupId').value;
        const name = document.getElementById('rowName').value.trim();
        if (!name) return;

        try {
            const response = await fetch('/api/timeline-rows', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    group_id: groupId,
                    name
                })
            });

            if (response.ok) {
                const data = await response.json();
                // Update local data
                const group = this.groups.find(g => g.id === groupId);
                if (group) {
                    group.rows.push(data.row);
                    this.renderGroups(); // Full re-render to update row list
                }
                this.hideRowModal();
            } else {
                console.error('Failed to create row');
            }
        } catch (error) {
            console.error('Error creating row:', error);
        }
    }

    async deleteGroup(groupId) {
        if (!confirm('Are you sure you want to delete this timeline group?')) return;

        try {
            const response = await fetch(`/api/timeline-groups/${groupId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                this.groups = this.groups.filter(g => g.id !== groupId);
                this.timelines.delete(groupId);
                this.renderGroups();
            } else {
                console.error('Failed to delete group');
            }
        } catch (error) {
            console.error('Error deleting group:', error);
        }
    }

    async deleteRow(groupId, rowId) {
        if (!confirm('Are you sure you want to delete this row?')) return;

        try {
            const response = await fetch(`/api/timeline-rows/${rowId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                // Update local data
                const group = this.groups.find(g => g.id === groupId);
                if (group) {
                    group.rows = group.rows.filter(r => r.id !== rowId);
                    this.renderGroups(); // Full re-render to update row list
                }
            } else {
                console.error('Failed to delete row');
            }
        } catch (error) {
            console.error('Error deleting row:', error);
        }
    }
}