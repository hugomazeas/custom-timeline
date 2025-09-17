import './bootstrap';
import { Timeline } from 'vis-timeline/standalone';

// Timeline Alpine.js component - defined globally to prevent loading order issues
document.addEventListener('alpine:init', () => {
    Alpine.data('timelineWidget', () => ({
        timeline: null,
        groupId: null,

        init() {
            // Listen for Livewire events to update timeline
            this.$wire.on('timeline-updated', (event) => {
                if (event.groupId === this.groupId) {
                    this.refreshTimeline();
                }
            });

            // Also refresh on livewire updates
            this.$wire.on('livewire:updated', () => {
                this.refreshTimeline();
            });
        },

        initTimeline(groupId, groupData) {
            this.groupId = groupId;
            const container = this.$el;

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
            this.timeline = new Timeline(container, items, groups, options);
        },

        refreshTimeline() {
            // Get updated data from Livewire component
            const groupData = this.$wire.groups.find(g => g.id === this.groupId);
            if (!groupData) return;

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

            // Update timeline with new data
            if (this.timeline) {
                this.timeline.setItems(items);
                this.timeline.setGroups(groups);
            }
        }
    }));
});

console.log('Timeline app loaded with Livewire + Alpine.js');
