<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProgramCompletion;
use App\Models\ProgramItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        ProgramCompletion::query()->delete();
        ProgramItem::query()->delete();
        User::query()
            ->where('role', User::ROLE_CLIENT)
            ->where('email', '!=', 'moemortezaei@gmail.com')
            ->delete();

        $categories = collect([
            ['name' => 'Morning', 'sort_order' => 1],
            ['name' => 'Evening', 'sort_order' => 2],
            ['name' => 'Night', 'sort_order' => 3],
        ])->map(fn (array $category) => Category::query()->updateOrCreate(
            ['slug' => str($category['name'])->slug()->value()],
            [
                'name' => $category['name'],
                'sort_order' => $category['sort_order'],
                'is_active' => true,
            ],
        ));

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'full_name' => 'Admin Manager',
                'phone' => '604-555-1000',
                'date_of_birth' => '1989-06-12',
                'password' => 'Shadi*1234',
                'role' => User::ROLE_ADMIN,
                'is_premium' => false,
            ],
        );

        $moe = User::query()->updateOrCreate(
            ['email' => 'moemortezaei@gmail.com'],
            [
                'full_name' => 'Moe Mortezaei',
                'phone' => '604-555-2300',
                'date_of_birth' => '1996-01-01',
                'password' => 'Moe*1234',
                'role' => User::ROLE_CLIENT,
                'is_premium' => false,
            ],
        );

        $today = Carbon::today();
        $yesterday = $today->copy()->subDay();

        $clients = collect([$moe]);

        $clients->each(function (User $client, int $index) use ($admin, $categories, $today, $yesterday): void {
            $skinRoutine = ProgramItem::query()->create([
                'user_id' => $client->id,
                'assigned_by' => $admin->id,
                'category_id' => $categories->firstWhere('name', 'Morning')?->id,
                'kind' => ProgramItem::KIND_ROUTINE,
                'recurrence_type' => ProgramItem::RECURRENCE_DAILY,
                'title' => 'Skin Care Routine',
                'content_type' => ProgramItem::CONTENT_TEXT,
                'content_body' => 'Cleanser, serum, moisturizer, and sunscreen before leaving the house.',
                'scheduled_date' => null,
                'starts_on' => $today->copy()->subWeek()->toDateString(),
                'ends_on' => null,
                'is_active' => true,
            ]);

            $stretchRoutine = ProgramItem::query()->create([
                'user_id' => $client->id,
                'assigned_by' => $admin->id,
                'category_id' => $categories->firstWhere('name', 'Night')?->id,
                'kind' => ProgramItem::KIND_ROUTINE,
                'recurrence_type' => ProgramItem::RECURRENCE_DAILY,
                'title' => 'Stretching',
                'content_type' => ProgramItem::CONTENT_VIDEO,
                'content_body' => 'Follow the video for a 5 minute stretch before bed.',
                'video_url' => 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4',
                'scheduled_date' => null,
                'starts_on' => $today->copy()->subDays(10)->toDateString(),
                'ends_on' => null,
                'is_active' => true,
            ]);

            foreach ($categories as $category) {
                ProgramItem::query()->create([
                    'user_id' => $client->id,
                    'assigned_by' => $admin->id,
                    'category_id' => $category->id,
                    'kind' => ProgramItem::KIND_CATEGORY_PROGRAM,
                    'recurrence_type' => ProgramItem::RECURRENCE_ONCE,
                    'title' => sprintf('%s Plan', $category->name),
                    'content_type' => $category->name === 'Evening'
                        ? ProgramItem::CONTENT_VIDEO
                        : ProgramItem::CONTENT_TEXT,
                    'content_body' => $category->name === 'Evening'
                        ? 'Watch the short clip and repeat the steps right after.'
                        : sprintf('Today\'s %s plan for %s.', strtolower($category->name), $client->full_name),
                    'video_url' => $category->name === 'Evening'
                        ? 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4'
                        : null,
                    'scheduled_date' => $today->toDateString(),
                    'starts_on' => null,
                    'ends_on' => null,
                    'is_active' => true,
                ]);
            }

            ProgramItem::query()->create([
                'user_id' => $client->id,
                'assigned_by' => $admin->id,
                'category_id' => $categories->firstWhere('name', 'Evening')?->id,
                'kind' => ProgramItem::KIND_ROUTINE,
                'recurrence_type' => ProgramItem::RECURRENCE_ONCE,
                'title' => 'Hydration Check',
                'content_type' => ProgramItem::CONTENT_TEXT,
                'content_body' => 'Drink two extra glasses of water today and log how you feel tonight.',
                'scheduled_date' => $today->toDateString(),
                'starts_on' => null,
                'ends_on' => null,
                'is_active' => true,
            ]);

            ProgramItem::query()->create([
                'user_id' => $client->id,
                'assigned_by' => $admin->id,
                'category_id' => $categories->firstWhere('name', 'Night')?->id,
                'kind' => ProgramItem::KIND_CATEGORY_PROGRAM,
                'recurrence_type' => ProgramItem::RECURRENCE_ONCE,
                'title' => 'Night Review',
                'content_type' => ProgramItem::CONTENT_TEXT,
                'content_body' => 'Write one sentence about what felt easiest yesterday.',
                'scheduled_date' => $yesterday->toDateString(),
                'starts_on' => null,
                'ends_on' => null,
                'is_active' => true,
            ]);

            if ($index === 0 || $client->is_premium) {
                $morningProgram = $client->programItems()
                    ->where('title', 'Morning Plan')
                    ->whereDate('scheduled_date', $today)
                    ->first();

                $eveningProgram = $client->programItems()
                    ->where('title', 'Evening Plan')
                    ->whereDate('scheduled_date', $today)
                    ->first();

                ProgramCompletion::query()->create([
                    'program_item_id' => $skinRoutine->id,
                    'user_id' => $client->id,
                    'completion_date' => $today->toDateString(),
                    'completed_at' => $today->copy()->setTime(8 + $index, 15),
                ]);

                ProgramCompletion::query()->create([
                    'program_item_id' => $morningProgram->id,
                    'user_id' => $client->id,
                    'completion_date' => $today->toDateString(),
                    'completed_at' => $today->copy()->setTime(9 + $index, 5),
                ]);

                ProgramCompletion::query()->create([
                    'program_item_id' => $eveningProgram->id,
                    'user_id' => $client->id,
                    'completion_date' => $today->toDateString(),
                    'completed_at' => $today->copy()->setTime(18, 40 + $index),
                ]);
            }

            if ($index === 1) {
                ProgramCompletion::query()->create([
                    'program_item_id' => $stretchRoutine->id,
                    'user_id' => $client->id,
                    'completion_date' => $today->toDateString(),
                    'completed_at' => $today->copy()->setTime(21, 10),
                ]);
            }
        });
    }
}
