import { test, expect, TimelineTestHelper, setupTestData, generateTestData } from './helpers/test-utils.js';

test.describe('Timeline Visualization', () => {
  let helper;

  test.beforeEach(async ({ page }) => {
    helper = await setupTestData(page);
  });

  test('should initialize vis-timeline for groups with events', async ({ page }) => {
    await helper.createGroup('Visualization Test Group');

    const group = page.locator('.timeline-group').first();
    const groupId = await group.getAttribute('data-group-id');
    const row = group.locator('.timeline-row').first();
    const rowId = await row.getAttribute('data-row-id');

    await helper.createEvent(groupId, rowId, {
      title: 'Test Event',
      type: 'punctual',
      start: '2024-01-15T10:00',
      color: '#3B82F6'
    });

    const timelineContainer = group.locator('.vis-timeline');
    await expect(timelineContainer).toBeVisible();

    const timelineInitialized = await page.evaluate(() => {
      return window.timelineApp && window.timelineApp.timelines.size > 0;
    });

    expect(timelineInitialized).toBe(true);
  });

  test('should render punctual events correctly on timeline', async ({ page }) => {
    await helper.createGroup('Punctual Events Group');

    const group = page.locator('.timeline-group').first();
    const groupId = await group.getAttribute('data-group-id');
    const row = group.locator('.timeline-row').first();
    const rowId = await row.getAttribute('data-row-id');

    const punctualEvents = [
      { title: 'Event 1', start: '2024-01-15T09:00', color: '#3B82F6' },
      { title: 'Event 2', start: '2024-01-16T14:00', color: '#EF4444' },
      { title: 'Event 3', start: '2024-01-17T11:30', color: '#10B981' }
    ];

    for (const eventData of punctualEvents) {
      await helper.createEvent(groupId, rowId, {
        ...eventData,
        type: 'punctual'
      });
    }

    const timelineContainer = group.locator('.vis-timeline');
    await expect(timelineContainer).toBeVisible();

    const timelineItems = await page.evaluate(() => {
      const timeline = window.timelineApp?.timelines?.values().next().value;
      return timeline ? timeline.itemsData.get() : [];
    });

    expect(timelineItems).toHaveLength(3);

    for (let i = 0; i < punctualEvents.length; i++) {
      const item = timelineItems.find(item => item.content === punctualEvents[i].title);
      expect(item).toBeDefined();
      expect(item.type).toBe('point');
      expect(item.style).toContain(`background-color: ${punctualEvents[i].color}`);
    }
  });

  test('should render timespan events correctly on timeline', async ({ page }) => {
    await helper.createGroup('Timespan Events Group');

    const group = page.locator('.timeline-group').first();
    const groupId = await group.getAttribute('data-group-id');
    const row = group.locator('.timeline-row').first();
    const rowId = await row.getAttribute('data-row-id');

    const timespanEvent = {
      title: 'Development Phase',
      type: 'timespan',
      start: '2024-01-15T09:00',
      end: '2024-01-30T18:00',
      color: '#F59E0B'
    };

    await helper.createEvent(groupId, rowId, timespanEvent);

    const timelineContainer = group.locator('.vis-timeline');
    await expect(timelineContainer).toBeVisible();

    const timelineItems = await page.evaluate(() => {
      const timeline = window.timelineApp?.timelines?.values().next().value;
      return timeline ? timeline.itemsData.get() : [];
    });

    expect(timelineItems).toHaveLength(1);

    const item = timelineItems[0];
    expect(item.content).toBe(timespanEvent.title);
    expect(item.type).toBe('range');
    expect(item.start).toBeDefined();
    expect(item.end).toBeDefined();
    expect(item.style).toContain(`background-color: ${timespanEvent.color}`);
  });

  test('should handle mixed event types on same timeline', async ({ page }) => {
    await helper.createGroup('Mixed Events Group');

    const group = page.locator('.timeline-group').first();
    const groupId = await group.getAttribute('data-group-id');
    const row = group.locator('.timeline-row').first();
    const rowId = await row.getAttribute('data-row-id');

    const mixedEvents = [
      { title: 'Kickoff', type: 'punctual', start: '2024-01-15T09:00', color: '#3B82F6' },
      { title: 'Development', type: 'timespan', start: '2024-01-16T09:00', end: '2024-01-25T18:00', color: '#10B981' },
      { title: 'Review', type: 'punctual', start: '2024-01-26T14:00', color: '#F59E0B' },
      { title: 'Testing', type: 'timespan', start: '2024-01-27T09:00', end: '2024-01-30T17:00', color: '#EF4444' }
    ];

    for (const eventData of mixedEvents) {
      await helper.createEvent(groupId, rowId, eventData);
    }

    const timelineContainer = group.locator('.vis-timeline');
    await expect(timelineContainer).toBeVisible();

    const timelineItems = await page.evaluate(() => {
      const timeline = window.timelineApp?.timelines?.values().next().value;
      return timeline ? timeline.itemsData.get() : [];
    });

    expect(timelineItems).toHaveLength(4);

    const punctualItems = timelineItems.filter(item => item.type === 'point');
    const timespanItems = timelineItems.filter(item => item.type === 'range');

    expect(punctualItems).toHaveLength(2);
    expect(timespanItems).toHaveLength(2);
  });

  test('should display multiple rows with separate timelines', async ({ page }) => {
    await helper.createGroup('Multi-Row Timeline Group');

    const group = page.locator('.timeline-group').first();
    const groupId = await group.getAttribute('data-group-id');

    await helper.createRow(groupId, 'Development Row');
    await helper.createRow(groupId, 'Testing Row');

    const rows = await group.locator('.timeline-row').all();

    for (let i = 1; i < rows.length; i++) {
      const row = rows[i];
      const rowId = await row.getAttribute('data-row-id');

      await helper.createEvent(groupId, rowId, {
        title: `Event for Row ${i}`,
        type: 'punctual',
        start: `2024-01-${String(15 + i).padStart(2, '0')}T10:00`,
        color: '#3B82F6'
      });
    }

    const timelineContainers = group.locator('.vis-timeline');
    await expect(timelineContainers.first()).toBeVisible();

    const timelineCount = await page.evaluate(() => {
      return window.timelineApp?.timelines?.size || 0;
    });

    expect(timelineCount).toBeGreaterThan(0);
  });

  test('should handle timeline interactions (zoom, pan)', async ({ page }) => {
    await helper.createGroup('Interactive Timeline Group');

    const group = page.locator('.timeline-group').first();
    const groupId = await group.getAttribute('data-group-id');
    const row = group.locator('.timeline-row').first();
    const rowId = await row.getAttribute('data-row-id');

    await helper.createEvent(groupId, rowId, {
      title: 'Interactive Event',
      type: 'punctual',
      start: '2024-01-15T10:00',
      color: '#3B82F6'
    });

    const timelineContainer = group.locator('.vis-timeline');
    await expect(timelineContainer).toBeVisible();

    const initialWindow = await page.evaluate(() => {
      const timeline = window.timelineApp?.timelines?.values().next().value;
      return timeline ? timeline.getWindow() : null;
    });

    expect(initialWindow).toBeDefined();
    expect(initialWindow.start).toBeDefined();
    expect(initialWindow.end).toBeDefined();

    await timelineContainer.click();
    await page.mouse.wheel(0, -100);

    await page.waitForTimeout(500);

    const afterZoomWindow = await page.evaluate(() => {
      const timeline = window.timelineApp?.timelines?.values().next().value;
      return timeline ? timeline.getWindow() : null;
    });

    expect(afterZoomWindow).toBeDefined();
  });

  test('should update timeline when events are added', async ({ page }) => {
    await helper.createGroup('Dynamic Timeline Group');

    const group = page.locator('.timeline-group').first();
    const groupId = await group.getAttribute('data-group-id');
    const row = group.locator('.timeline-row').first();
    const rowId = await row.getAttribute('data-row-id');

    await helper.createEvent(groupId, rowId, {
      title: 'Initial Event',
      type: 'punctual',
      start: '2024-01-15T10:00',
      color: '#3B82F6'
    });

    let timelineItems = await page.evaluate(() => {
      const timeline = window.timelineApp?.timelines?.values().next().value;
      return timeline ? timeline.itemsData.get() : [];
    });

    expect(timelineItems).toHaveLength(1);

    await helper.createEvent(groupId, rowId, {
      title: 'Additional Event',
      type: 'punctual',
      start: '2024-01-16T14:00',
      color: '#EF4444'
    });

    timelineItems = await page.evaluate(() => {
      const timeline = window.timelineApp?.timelines?.values().next().value;
      return timeline ? timeline.itemsData.get() : [];
    });

    expect(timelineItems).toHaveLength(2);
  });

  test('should update timeline when events are deleted', async ({ page }) => {
    await helper.createGroup('Event Deletion Timeline Group');

    const group = page.locator('.timeline-group').first();
    const groupId = await group.getAttribute('data-group-id');
    const row = group.locator('.timeline-row').first();
    const rowId = await row.getAttribute('data-row-id');

    await helper.createEvent(groupId, rowId, {
      title: 'Event to Keep',
      type: 'punctual',
      start: '2024-01-15T10:00',
      color: '#3B82F6'
    });

    await helper.createEvent(groupId, rowId, {
      title: 'Event to Delete',
      type: 'punctual',
      start: '2024-01-16T14:00',
      color: '#EF4444'
    });

    let timelineItems = await page.evaluate(() => {
      const timeline = window.timelineApp?.timelines?.values().next().value;
      return timeline ? timeline.itemsData.get() : [];
    });

    expect(timelineItems).toHaveLength(2);

    const eventToDelete = row.locator('.timeline-event').filter({ hasText: 'Event to Delete' });
    const eventId = await eventToDelete.getAttribute('data-event-id');

    await helper.deleteEvent(eventId);

    timelineItems = await page.evaluate(() => {
      const timeline = window.timelineApp?.timelines?.values().next().value;
      return timeline ? timeline.itemsData.get() : [];
    });

    expect(timelineItems).toHaveLength(1);
    expect(timelineItems[0].content).toBe('Event to Keep');
  });

  test('should handle timeline responsiveness at different viewport sizes', async ({ page }) => {
    await helper.createGroup('Responsive Timeline Group');

    const group = page.locator('.timeline-group').first();
    const groupId = await group.getAttribute('data-group-id');
    const row = group.locator('.timeline-row').first();
    const rowId = await row.getAttribute('data-row-id');

    await helper.createEvent(groupId, rowId, {
      title: 'Responsive Event',
      type: 'punctual',
      start: '2024-01-15T10:00',
      color: '#3B82F6'
    });

    const timelineContainer = group.locator('.vis-timeline');
    await expect(timelineContainer).toBeVisible();

    const desktopSize = await timelineContainer.boundingBox();
    expect(desktopSize.width).toBeGreaterThan(800);

    await page.setViewportSize({ width: 768, height: 1024 });
    await page.waitForTimeout(500);

    const tabletSize = await timelineContainer.boundingBox();
    expect(tabletSize.width).toBeLessThan(desktopSize.width);
    expect(tabletSize.width).toBeGreaterThan(400);

    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(500);

    const mobileSize = await timelineContainer.boundingBox();
    expect(mobileSize.width).toBeLessThan(tabletSize.width);
  });

  test('should maintain timeline performance with many events', async ({ page }) => {
    await helper.createGroup('Performance Test Group');

    const group = page.locator('.timeline-group').first();
    const groupId = await group.getAttribute('data-group-id');
    const row = group.locator('.timeline-row').first();
    const rowId = await row.getAttribute('data-row-id');

    const eventCount = 25;

    for (let i = 1; i <= eventCount; i++) {
      await helper.createEvent(groupId, rowId, {
        title: `Event ${i}`,
        type: i % 3 === 0 ? 'timespan' : 'punctual',
        start: `2024-01-${String(Math.floor(i / 2) + 1).padStart(2, '0')}T${String(9 + (i % 8)).padStart(2, '0')}:00`,
        end: i % 3 === 0 ? `2024-01-${String(Math.floor(i / 2) + 2).padStart(2, '0')}T18:00` : undefined,
        color: ['#3B82F6', '#EF4444', '#10B981'][i % 3]
      });

      if (i % 5 === 0) {
        await page.waitForTimeout(100);
      }
    }

    const timelineContainer = group.locator('.vis-timeline');
    await expect(timelineContainer).toBeVisible();

    const timelineItems = await page.evaluate(() => {
      const timeline = window.timelineApp?.timelines?.values().next().value;
      return timeline ? timeline.itemsData.get() : [];
    });

    expect(timelineItems).toHaveLength(eventCount);

    const renderTime = await page.evaluate(() => {
      const start = performance.now();
      const timeline = window.timelineApp?.timelines?.values().next().value;
      if (timeline) {
        timeline.redraw();
      }
      return performance.now() - start;
    });

    expect(renderTime).toBeLessThan(1000);
  });

  test('should preserve timeline state after page reload', async ({ page }) => {
    await helper.createGroup('State Persistence Group');

    const group = page.locator('.timeline-group').first();
    const groupId = await group.getAttribute('data-group-id');
    const row = group.locator('.timeline-row').first();
    const rowId = await row.getAttribute('data-row-id');

    const testEvents = [
      { title: 'Persistent Event 1', type: 'punctual', start: '2024-01-15T10:00', color: '#3B82F6' },
      { title: 'Persistent Event 2', type: 'timespan', start: '2024-01-16T09:00', end: '2024-01-20T18:00', color: '#10B981' }
    ];

    for (const eventData of testEvents) {
      await helper.createEvent(groupId, rowId, eventData);
    }

    const beforeReloadItems = await page.evaluate(() => {
      const timeline = window.timelineApp?.timelines?.values().next().value;
      return timeline ? timeline.itemsData.get() : [];
    });

    await page.reload();
    await page.waitForLoadState('networkidle');

    const reloadedGroup = page.locator('.timeline-group').first();
    const timelineContainer = reloadedGroup.locator('.vis-timeline');
    await expect(timelineContainer).toBeVisible();

    const afterReloadItems = await page.evaluate(() => {
      const timeline = window.timelineApp?.timelines?.values().next().value;
      return timeline ? timeline.itemsData.get() : [];
    });

    expect(afterReloadItems).toHaveLength(beforeReloadItems.length);

    for (const originalItem of beforeReloadItems) {
      const matchingItem = afterReloadItems.find(item => item.content === originalItem.content);
      expect(matchingItem).toBeDefined();
      expect(matchingItem.type).toBe(originalItem.type);
    }
  });
});