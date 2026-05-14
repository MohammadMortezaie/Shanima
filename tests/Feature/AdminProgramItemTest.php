<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ProgramItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProgramItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_a_category_to_a_routine(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $client = User::query()->where('email', 'moemortezaei@gmail.com')->firstOrFail();
        $category = Category::query()->where('slug', 'morning')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.clients.program-items.store', $client), [
            'kind' => ProgramItem::KIND_ROUTINE,
            'title' => 'Morning Reset',
            'category_id' => $category->id,
            'content_type' => ProgramItem::CONTENT_TEXT,
            'content_body' => 'Wash face and drink water.',
            'video_url' => null,
            'recurrence_type' => ProgramItem::RECURRENCE_DAILY,
            'scheduled_date' => null,
            'starts_on' => now()->toDateString(),
            'ends_on' => null,
            'is_active' => 1,
            'return_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('admin.clients.show', [
            'client' => $client,
            'date' => now()->toDateString(),
        ]));

        $this->assertDatabaseHas('program_items', [
            'user_id' => $client->id,
            'assigned_by' => $admin->id,
            'category_id' => $category->id,
            'kind' => ProgramItem::KIND_ROUTINE,
            'recurrence_type' => ProgramItem::RECURRENCE_DAILY,
            'title' => 'Morning Reset',
        ]);
    }
}
