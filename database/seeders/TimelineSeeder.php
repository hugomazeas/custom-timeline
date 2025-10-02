<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimelineSeeder extends Seeder
{
    public function run(): void
    {
        $groupId = DB::table('timeline_groups')->insertGetId([
            'name' => 'October 2025 Project Timeline',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rows = [
            ['name' => 'Development', 'id' => null],
            ['name' => 'Design', 'id' => null],
            ['name' => 'Marketing', 'id' => null],
            ['name' => 'Testing', 'id' => null],
        ];

        foreach ($rows as &$row) {
            $row['id'] = DB::table('timeline_rows')->insertGetId([
                'timeline_group_id' => $groupId,
                'name' => $row['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $events = [
            [
                'row' => 'Development',
                'title' => 'Sprint Planning',
                'start' => '2025-10-01 09:00:00',
                'end' => null,
                'color' => '#6366f1',
            ],
            [
                'row' => 'Development',
                'title' => 'Feature Kickoff: User Auth',
                'start' => '2025-10-02 10:00:00',
                'end' => null,
                'color' => '#8b5cf6',
            ],
            [
                'row' => 'Development',
                'title' => 'Database Review',
                'start' => '2025-10-08 09:00:00',
                'end' => null,
                'color' => '#6366f1',
            ],
            [
                'row' => 'Development',
                'title' => 'API Integration Start',
                'start' => '2025-10-14 10:00:00',
                'end' => null,
                'color' => '#8b5cf6',
            ],
            [
                'row' => 'Development',
                'title' => 'Code Review Session',
                'start' => '2025-10-22 14:00:00',
                'end' => null,
                'color' => '#3b82f6',
            ],
            [
                'row' => 'Development',
                'title' => 'Deployment Prep',
                'start' => '2025-10-28 11:00:00',
                'end' => null,
                'color' => '#6366f1',
            ],
            [
                'row' => 'Design',
                'title' => 'UI Mockups Review',
                'start' => '2025-10-01 14:00:00',
                'end' => null,
                'color' => '#ec4899',
            ],
            [
                'row' => 'Design',
                'title' => 'Design System Workshop',
                'start' => '2025-10-03 09:00:00',
                'end' => null,
                'color' => '#f43f5e',
            ],
            [
                'row' => 'Design',
                'title' => 'User Flow Meeting',
                'start' => '2025-10-09 10:00:00',
                'end' => null,
                'color' => '#ec4899',
            ],
            [
                'row' => 'Design',
                'title' => 'Component Library Update',
                'start' => '2025-10-15 14:00:00',
                'end' => null,
                'color' => '#f43f5e',
            ],
            [
                'row' => 'Design',
                'title' => 'Final Design Handoff',
                'start' => '2025-10-20 11:00:00',
                'end' => null,
                'color' => '#ec4899',
            ],
            [
                'row' => 'Marketing',
                'title' => 'Content Strategy Meeting',
                'start' => '2025-10-02 15:00:00',
                'end' => null,
                'color' => '#10b981',
            ],
            [
                'row' => 'Marketing',
                'title' => 'Social Media Kickoff',
                'start' => '2025-10-06 09:00:00',
                'end' => null,
                'color' => '#14b8a6',
            ],
            [
                'row' => 'Marketing',
                'title' => 'Influencer Outreach',
                'start' => '2025-10-12 10:00:00',
                'end' => null,
                'color' => '#10b981',
            ],
            [
                'row' => 'Marketing',
                'title' => 'Newsletter Draft Review',
                'start' => '2025-10-16 10:00:00',
                'end' => null,
                'color' => '#14b8a6',
            ],
            [
                'row' => 'Marketing',
                'title' => 'Launch Campaign Start',
                'start' => '2025-10-25 08:00:00',
                'end' => null,
                'color' => '#10b981',
            ],
            [
                'row' => 'Testing',
                'title' => 'Test Plan Review',
                'start' => '2025-10-07 09:00:00',
                'end' => null,
                'color' => '#f59e0b',
            ],
            [
                'row' => 'Testing',
                'title' => 'Integration Test Session',
                'start' => '2025-10-13 10:00:00',
                'end' => null,
                'color' => '#f97316',
            ],
            [
                'row' => 'Testing',
                'title' => 'UAT Kickoff',
                'start' => '2025-10-21 09:00:00',
                'end' => null,
                'color' => '#f59e0b',
            ],
            [
                'row' => 'Testing',
                'title' => 'Bug Triage Meeting',
                'start' => '2025-10-26 10:00:00',
                'end' => null,
                'color' => '#f97316',
            ],
            [
                'row' => 'Testing',
                'title' => 'Final QA Sign-off',
                'start' => '2025-10-30 15:00:00',
                'end' => null,
                'color' => '#f59e0b',
            ],
        ];

        foreach ($events as $event) {
            $rowId = collect($rows)->firstWhere('name', $event['row'])['id'];

            DB::table('events')->insert([
                'timeline_row_id' => $rowId,
                'title' => $event['title'],
                'start_date' => $event['start'],
                'end_date' => $event['end'],
                'color' => $event['color'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
