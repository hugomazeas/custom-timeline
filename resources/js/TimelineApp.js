import { Timeline } from 'vis-timeline/standalone';

export class TimelineApp {
    constructor() {
        this.groups = [];
        this.timelines = new Map();
        this.contextMenuOpen = null;
        this.comparisonTimeline = null;
        this.sourceRowForComparison = null;
        this.rowToMove = null;
        this.init();
    }

    init() {
        this.bindEventListeners();
        this.loadGroups();
        this.setupGlobalContextMenuClose();
    }

    setupGlobalContextMenuClose() {
        document.addEventListener('click', (e) => {
            if (this.contextMenuOpen && !e.target.closest('.context-menu') && !e.target.closest('[data-context-menu-trigger]')) {
                this.closeContextMenu();
            }
        });
    }

    closeContextMenu() {
        if (this.contextMenuOpen) {
            this.contextMenuOpen.remove();
            this.contextMenuOpen = null;
        }
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

        // Compare modals
        addListener('cancelCompareSelectionBtn', 'click', () => this.hideCompareSelectionModal());
        addListener('closeComparisonBtn', 'click', () => this.hideComparisonModal());

        // Move row modal
        addListener('cancelMoveRowBtn', 'click', () => this.hideMoveRowModal());

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
        div.className = 'bg-[#2a2a2a] rounded-lg overflow-hidden';

        const allEvents = [];
        group.rows.forEach(row => {
            row.events.forEach(event => {
                allEvents.push({
                    ...event,
                    rowName: row.name,
                    rowId: row.id
                });
            });
        });

        if (!group.hiddenRows) {
            group.hiddenRows = new Set();
        }

        div.innerHTML = `
            <div class="p-5 border-b border-gray-800">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-medium text-gray-300">${group.name}</h2>
                    <div class="flex items-center space-x-2">
                        <button onclick="timelineApp.showRowModal('${group.id}')" class="bg-[#323232] hover:bg-[#3a3a3a] text-gray-400 hover:text-[#e2b714] font-medium py-2 px-3 rounded transition-colors duration-200 flex items-center space-x-1.5 text-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>add row</span>
                        </button>
                        <button onclick="timelineApp.deleteGroup('${group.id}')" class="bg-[#323232] hover:bg-[#3a3a3a] text-gray-400 hover:text-red-500 font-medium py-2 px-3 rounded transition-colors duration-200 text-sm">
                            delete
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="mb-4">
                    <div class="flex items-center gap-2 flex-wrap" id="rows-${group.id}">
                        ${group.rows.map(row => {
                            const isHidden = group.hiddenRows && group.hiddenRows.has(row.id);
                            return `
                            <div class="relative flex items-center gap-2 px-3 py-2 bg-[#323232] rounded hover:bg-[#3a3a3a] transition-colors duration-200">
                                <button onclick="timelineApp.toggleRowVisibility('${group.id}', '${row.id}')" class="text-gray-500 hover:text-[#e2b714] p-0.5 rounded transition-colors duration-200" title="${isHidden ? 'Show Row' : 'Hide Row'}">
                                    ${isHidden ? `
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                        </svg>
                                    ` : `
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    `}
                                </button>
                                <span class="font-medium text-gray-400 text-sm ${isHidden ? 'line-through opacity-50' : ''}">${row.name}</span>
                                <div class="flex items-center gap-1">
                                    <button onclick="timelineApp.showEventModal('${group.id}', '${row.id}')" class="text-gray-500 hover:text-[#e2b714] p-1 rounded transition-colors duration-200" title="Add Event">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </button>
                                    <button onclick="timelineApp.showRowContextMenu(event, '${group.id}', '${row.id}', '${row.name}')" data-context-menu-trigger class="text-gray-500 hover:text-[#e2b714] p-1 rounded transition-colors duration-200" title="Row Options">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        `}).join('')}
                    </div>
                </div>
                <div class="grid grid-cols-12 gap-6">
                    <div class="col-span-9">
                        <div class="mb-2 flex items-center gap-2">
                            <button onclick="timelineApp.resetZoom('${group.id}')" class="bg-[#323232] hover:bg-[#3a3a3a] text-gray-400 hover:text-[#e2b714] font-medium py-1.5 px-3 rounded transition-colors duration-200 text-xs flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
                                </svg>
                                <span>reset zoom</span>
                            </button>
                        </div>
                        <div id="timeline-${group.id}" class="h-[600px] bg-[#1a1a1a] rounded border border-gray-800"></div>
                    </div>
                    <div class="col-span-3">
                        <h3 class="text-xs font-medium text-gray-500 mb-3 uppercase tracking-wider">events</h3>
                        <div class="space-y-1.5 max-h-[600px] overflow-y-auto" id="events-${group.id}">
                            ${allEvents.length > 0 ? allEvents.map(event => `
                                <div class="p-2.5 bg-[#323232] rounded hover:bg-[#3a3a3a] transition-colors duration-200">
                                    <div class="flex items-start justify-between mb-1">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: ${event.color}"></div>
                                                <span class="font-medium text-gray-300 text-sm truncate">${event.title}</span>
                                            </div>
                                            <div class="text-xs text-gray-500 ml-5">${event.rowName}</div>
                                            <div class="text-xs text-gray-600 ml-5">${new Date(event.start).toLocaleDateString()}</div>
                                        </div>
                                        <div class="flex items-center space-x-1 ml-2">
                                            <button onclick="timelineApp.editEvent('${group.id}', '${event.rowId}', '${event.id}')" class="text-gray-500 hover:text-[#e2b714] p-1 rounded transition-colors duration-200" title="Edit Event">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="timelineApp.deleteEvent('${group.id}', '${event.id}')" class="text-gray-500 hover:text-red-500 p-1 rounded transition-colors duration-200" title="Delete Event">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `).join('') : '<div class="text-center py-8 text-gray-600 text-sm">no events yet</div>'}
                        </div>
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

        if (!groupData.hiddenRows) {
            groupData.hiddenRows = new Set();
        }

        // Prepare vis-timeline data
        const items = [];
        const groups = [];

        groupData.rows.forEach(row => {
            if (!groupData.hiddenRows.has(row.id)) {
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
            }
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
        document.getElementById('eventModalTitle').textContent = 'add event';
        document.getElementById('eventGroupId').value = groupId;
        document.getElementById('eventRowId').value = rowId;
        document.getElementById('eventId').value = '';

        // Reset form
        document.getElementById('eventTitle').value = '';
        document.getElementById('eventStart').value = '';
        document.getElementById('eventEnd').value = '';
        document.getElementById('eventColor').value = '#6366f1';
        document.getElementById('eventColorHex').value = '#6366f1';
        document.getElementById('eventIsDeadline').checked = false;
        document.getElementById('eventNotes').value = '';
        document.getElementById('eventLinks').value = '';
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
        const eventId = document.getElementById('eventId').value;
        const groupId = parseInt(document.getElementById('eventGroupId').value);
        const rowId = parseInt(document.getElementById('eventRowId').value);
        const title = document.getElementById('eventTitle').value.trim();
        const type = document.querySelector('input[name="eventType"]:checked').value;
        const start = document.getElementById('eventStart').value;
        const end = document.getElementById('eventEnd').value;
        const color = document.getElementById('eventColor').value;
        const isDeadline = document.getElementById('eventIsDeadline').checked;
        const notes = document.getElementById('eventNotes').value.trim();
        const links = document.getElementById('eventLinks').value.trim();

        if (!title || !start) return;

        const isEditing = eventId !== '';

        try {
            const url = isEditing ? `/api/timeline-events/${eventId}` : '/api/timeline-events';
            const method = isEditing ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method,
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
                    color,
                    is_deadline: isDeadline,
                    notes: notes || null,
                    links: links || null
                })
            });

            if (response.ok) {
                const data = await response.json();
                const group = this.groups.find(g => g.id === groupId);
                if (group) {
                    const row = group.rows.find(r => r.id === rowId);
                    if (row) {
                        if (isEditing) {
                            const eventIndex = row.events.findIndex(e => e.id === parseInt(eventId));
                            if (eventIndex !== -1) {
                                row.events[eventIndex] = data.event;
                            }
                        } else {
                            row.events.push(data.event);
                        }
                        this.renderGroups();
                    }
                }
                this.hideEventModal();
            } else {
                console.error(isEditing ? 'Failed to update event' : 'Failed to create event');
            }
        } catch (error) {
            console.error('Error saving event:', error);
        }
    }

    async createRow() {
        const groupId = parseInt(document.getElementById('rowGroupId').value);
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
                const group = this.groups.find(g => g.id === groupId);
                if (group) {
                    group.rows.push(data.row);
                    this.renderGroups();
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
        groupId = parseInt(groupId);
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
        groupId = parseInt(groupId);
        rowId = parseInt(rowId);
        if (!confirm('Are you sure you want to delete this row?')) return;

        try {
            const response = await fetch(`/api/timeline-rows/${rowId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                const group = this.groups.find(g => g.id === groupId);
                if (group) {
                    group.rows = group.rows.filter(r => r.id !== rowId);
                    this.renderGroups();
                }
            } else {
                console.error('Failed to delete row');
            }
        } catch (error) {
            console.error('Error deleting row:', error);
        }
    }

    async deleteEvent(groupId, eventId) {
        groupId = parseInt(groupId);
        eventId = parseInt(eventId);
        if (!confirm('Are you sure you want to delete this event?')) return;

        try {
            const response = await fetch(`/api/timeline-events/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                const group = this.groups.find(g => g.id === groupId);
                if (group) {
                    group.rows.forEach(row => {
                        row.events = row.events.filter(e => e.id !== eventId);
                    });
                    this.renderGroups();
                }
            } else {
                console.error('Failed to delete event');
            }
        } catch (error) {
            console.error('Error deleting event:', error);
        }
    }

    editEvent(groupId, rowId, eventId) {
        groupId = parseInt(groupId);
        rowId = parseInt(rowId);
        eventId = parseInt(eventId);

        const group = this.groups.find(g => g.id === groupId);
        if (!group) return;

        const row = group.rows.find(r => r.id === rowId);
        if (!row) return;

        const event = row.events.find(e => e.id === eventId);
        if (!event) return;

        document.getElementById('eventModal').classList.remove('hidden');
        document.getElementById('eventModal').classList.add('flex');
        document.getElementById('eventModalTitle').textContent = 'edit event';
        document.getElementById('eventGroupId').value = groupId;
        document.getElementById('eventRowId').value = rowId;
        document.getElementById('eventId').value = eventId;

        document.getElementById('eventTitle').value = event.title;
        document.getElementById('eventStart').value = event.start;
        document.getElementById('eventEnd').value = event.end || '';
        document.getElementById('eventColor').value = event.color;
        document.getElementById('eventColorHex').value = event.color;
        document.getElementById('eventIsDeadline').checked = event.is_deadline || false;
        document.getElementById('eventNotes').value = event.notes || '';
        document.getElementById('eventLinks').value = event.links || '';

        const eventType = event.type || (event.end ? 'timespan' : 'punctual');
        document.querySelector(`input[name="eventType"][value="${eventType}"]`).checked = true;
        this.toggleEndDateField();

        document.getElementById('eventTitle').focus();
    }

    toggleRowVisibility(groupId, rowId) {
        groupId = parseInt(groupId);
        rowId = parseInt(rowId);

        const group = this.groups.find(g => g.id === groupId);
        if (!group) return;

        if (!group.hiddenRows) {
            group.hiddenRows = new Set();
        }

        if (group.hiddenRows.has(rowId)) {
            group.hiddenRows.delete(rowId);
        } else {
            group.hiddenRows.add(rowId);
        }

        this.renderGroups();
    }

    resetZoom(groupId) {
        groupId = parseInt(groupId);
        const timeline = this.timelines.get(groupId);
        if (!timeline) {
            console.warn('Timeline not found for group:', groupId);
            return;
        }

        timeline.fit();
    }

    showRowContextMenu(event, groupId, rowId, rowName) {
        event.preventDefault();
        event.stopPropagation();

        this.closeContextMenu();

        const button = event.currentTarget;
        const rect = button.getBoundingClientRect();

        const menu = document.createElement('div');
        menu.className = 'context-menu fixed bg-[#2a2a2a] border border-gray-700 rounded-lg shadow-xl z-50 py-1 min-w-[180px]';
        menu.style.left = `${rect.left}px`;
        menu.style.top = `${rect.bottom + 4}px`;

        menu.innerHTML = `
            <button class="context-menu-item w-full px-4 py-2.5 text-left text-sm text-gray-300 hover:bg-[#323232] hover:text-[#e2b714] transition-colors duration-200 flex items-center gap-3" onclick="timelineApp.editRow('${groupId}', '${rowId}', '${rowName}'); timelineApp.closeContextMenu();">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>edit row</span>
            </button>
            <button class="context-menu-item w-full px-4 py-2.5 text-left text-sm text-gray-300 hover:bg-[#323232] hover:text-[#e2b714] transition-colors duration-200 flex items-center gap-3" onclick="timelineApp.showMoveRowModal('${groupId}', '${rowId}', '${rowName}'); timelineApp.closeContextMenu();">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span>move to group</span>
            </button>
            <button class="context-menu-item w-full px-4 py-2.5 text-left text-sm text-gray-300 hover:bg-[#323232] hover:text-red-500 transition-colors duration-200 flex items-center gap-3" onclick="timelineApp.deleteRow('${groupId}', '${rowId}'); timelineApp.closeContextMenu();">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                <span>delete row</span>
            </button>
            <div class="border-t border-gray-700 my-1"></div>
            <button class="context-menu-item w-full px-4 py-2.5 text-left text-sm text-gray-300 hover:bg-[#323232] hover:text-[#e2b714] transition-colors duration-200 flex items-center gap-3" onclick="timelineApp.showCompareSelectionModal('${groupId}', '${rowId}', '${rowName}'); timelineApp.closeContextMenu();">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span>compare rows</span>
            </button>
            <button class="context-menu-item w-full px-4 py-2.5 text-left text-sm text-gray-400 hover:bg-[#323232] hover:text-gray-300 transition-colors duration-200 flex items-center gap-3 opacity-50 cursor-not-allowed" disabled title="Coming soon">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span>anniversary notifications</span>
            </button>
        `;

        document.body.appendChild(menu);
        this.contextMenuOpen = menu;

        const menuRect = menu.getBoundingClientRect();
        if (menuRect.right > window.innerWidth) {
            menu.style.left = `${rect.right - menuRect.width}px`;
        }
        if (menuRect.bottom > window.innerHeight) {
            menu.style.top = `${rect.top - menuRect.height - 4}px`;
        }
    }

    editRow(groupId, rowId, currentName) {
        groupId = parseInt(groupId);
        rowId = parseInt(rowId);

        const newName = prompt('Enter new row name:', currentName);
        if (!newName || newName.trim() === '' || newName === currentName) {
            return;
        }

        console.log(`Edit row ${rowId} in group ${groupId} to: ${newName}`);
        alert('Edit row functionality will be implemented in the next update.');
    }

    showCompareSelectionModal(groupId, rowId, rowName) {
        groupId = parseInt(groupId);
        rowId = parseInt(rowId);

        this.sourceRowForComparison = {
            groupId,
            rowId,
            rowName
        };

        document.getElementById('sourceRowName').textContent = rowName;

        const listContainer = document.getElementById('rowSelectionList');
        listContainer.innerHTML = '';

        const sourceGroup = this.groups.find(g => g.id === groupId);
        if (!sourceGroup) return;

        const currentGroupRows = sourceGroup.rows.filter(r => r.id !== rowId);
        if (currentGroupRows.length > 0) {
            const groupHeader = document.createElement('div');
            groupHeader.className = 'text-xs font-medium text-gray-500 uppercase tracking-wider mb-2 mt-3 first:mt-0';
            groupHeader.textContent = sourceGroup.name;
            listContainer.appendChild(groupHeader);

            currentGroupRows.forEach(row => {
                const rowButton = document.createElement('button');
                rowButton.className = 'w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-[#323232] hover:text-[#e2b714] transition-colors duration-200 rounded flex items-center gap-2 ml-4';
                rowButton.innerHTML = `
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span>${row.name}</span>
                `;
                rowButton.onclick = () => this.compareRows(sourceGroup.id, rowId, sourceGroup.id, row.id);
                listContainer.appendChild(rowButton);
            });
        }

        this.groups.filter(g => g.id !== groupId).forEach(group => {
            if (group.rows.length > 0) {
                const groupHeader = document.createElement('div');
                groupHeader.className = 'text-xs font-medium text-gray-500 uppercase tracking-wider mb-2 mt-4';
                groupHeader.textContent = group.name;
                listContainer.appendChild(groupHeader);

                group.rows.forEach(row => {
                    const rowButton = document.createElement('button');
                    rowButton.className = 'w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-[#323232] hover:text-[#e2b714] transition-colors duration-200 rounded flex items-center gap-2 ml-4';
                    rowButton.innerHTML = `
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span>${row.name}</span>
                    `;
                    rowButton.onclick = () => this.compareRows(sourceGroup.id, rowId, group.id, row.id);
                    listContainer.appendChild(rowButton);
                });
            }
        });

        document.getElementById('compareSelectionModal').classList.remove('hidden');
        document.getElementById('compareSelectionModal').classList.add('flex');
    }

    hideCompareSelectionModal() {
        document.getElementById('compareSelectionModal').classList.add('hidden');
        document.getElementById('compareSelectionModal').classList.remove('flex');
        this.sourceRowForComparison = null;
    }

    compareRows(sourceGroupId, sourceRowId, targetGroupId, targetRowId) {
        sourceGroupId = parseInt(sourceGroupId);
        sourceRowId = parseInt(sourceRowId);
        targetGroupId = parseInt(targetGroupId);
        targetRowId = parseInt(targetRowId);

        this.hideCompareSelectionModal();

        const sourceGroup = this.groups.find(g => g.id === sourceGroupId);
        const targetGroup = this.groups.find(g => g.id === targetGroupId);

        if (!sourceGroup || !targetGroup) return;

        const sourceRow = sourceGroup.rows.find(r => r.id === sourceRowId);
        const targetRow = targetGroup.rows.find(r => r.id === targetRowId);

        if (!sourceRow || !targetRow) return;

        const container = document.getElementById('comparisonTimeline');
        if (!container) return;

        const items = [];
        const groups = [
            { id: 1, content: sourceRow.name },
            { id: 2, content: targetRow.name }
        ];

        sourceRow.events.forEach(event => {
            const item = {
                id: `source-${event.id}`,
                content: event.title,
                start: new Date(event.start),
                group: 1,
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

        targetRow.events.forEach(event => {
            const item = {
                id: `target-${event.id}`,
                content: event.title,
                start: new Date(event.start),
                group: 2,
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

        const options = {
            height: '100%',
            stack: true,
            orientation: 'top',
            showCurrentTime: true,
            zoomMin: 1000 * 60 * 60 * 24,
            zoomMax: 1000 * 60 * 60 * 24 * 365 * 10,
            editable: false,
            selectable: false,
            margin: {
                item: 10,
                axis: 20
            }
        };

        if (this.comparisonTimeline) {
            this.comparisonTimeline.destroy();
        }

        this.comparisonTimeline = new Timeline(container, items, groups, options);

        document.getElementById('comparisonModal').classList.remove('hidden');
        document.getElementById('comparisonModal').classList.add('flex');
    }

    hideComparisonModal() {
        document.getElementById('comparisonModal').classList.add('hidden');
        document.getElementById('comparisonModal').classList.remove('flex');

        if (this.comparisonTimeline) {
            this.comparisonTimeline.destroy();
            this.comparisonTimeline = null;
        }
    }

    showMoveRowModal(groupId, rowId, rowName) {
        groupId = parseInt(groupId);
        rowId = parseInt(rowId);

        this.rowToMove = { groupId, rowId, rowName };

        document.getElementById('moveRowName').textContent = rowName;

        const listContainer = document.getElementById('groupSelectionList');
        listContainer.innerHTML = '';

        this.groups.filter(g => g.id !== groupId).forEach(group => {
            const groupButton = document.createElement('button');
            groupButton.className = 'w-full text-left px-4 py-2.5 text-sm text-gray-300 hover:bg-[#323232] hover:text-[#e2b714] transition-colors duration-200 rounded flex items-center gap-2';
            groupButton.innerHTML = `
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span>${group.name}</span>
            `;
            groupButton.onclick = () => this.moveRow(rowId, group.id);
            listContainer.appendChild(groupButton);
        });

        if (this.groups.filter(g => g.id !== groupId).length === 0) {
            listContainer.innerHTML = '<div class="text-center py-8 text-gray-600 text-sm">no other groups available</div>';
        }

        document.getElementById('moveRowModal').classList.remove('hidden');
        document.getElementById('moveRowModal').classList.add('flex');
    }

    hideMoveRowModal() {
        document.getElementById('moveRowModal').classList.add('hidden');
        document.getElementById('moveRowModal').classList.remove('flex');
        this.rowToMove = null;
    }

    async moveRow(rowId, targetGroupId) {
        rowId = parseInt(rowId);
        targetGroupId = parseInt(targetGroupId);

        this.hideMoveRowModal();

        try {
            const response = await fetch(`/api/timeline-rows/${rowId}/move`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    target_group_id: targetGroupId
                })
            });

            if (response.ok) {
                await this.loadGroups();
            } else {
                console.error('Failed to move row');
            }
        } catch (error) {
            console.error('Error moving row:', error);
        }
    }
}