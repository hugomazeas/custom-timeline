import './bootstrap';
import { TimelineApp } from './TimelineApp.js';

// Initialize the timeline app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.timelineApp = new TimelineApp();
    console.log('Pure JavaScript Timeline App initialized');
});
