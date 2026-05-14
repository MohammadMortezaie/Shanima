<?php

namespace Tests\Feature;

use App\Models\ProgramCompletion;
use App\Models\ProgramItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_mark_a_due_item_as_done(): void
    {
        $this->seed();

        $client = User::query()->where('email', 'moemortezaei@gmail.com')->firstOrFail();
        $item = ProgramItem::query()
            ->where('user_id', $client->id)
            ->where('title', 'Night Plan')
            ->firstOrFail();

        $response = $this->actingAs($client)->post(route('client.completions.store', $item), [
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('client.dashboard', ['date' => now()->toDateString()]));

        $this->assertTrue(
            ProgramCompletion::query()
                ->where('program_item_id', $item->id)
                ->where('user_id', $client->id)
                ->whereDate('completion_date', now()->toDateString())
                ->exists()
        );
    }

    public function test_client_keeps_the_category_filter_after_marking_an_item_done(): void
    {
        $this->seed();

        $client = User::query()->where('email', 'moemortezaei@gmail.com')->firstOrFail();
        $item = ProgramItem::query()
            ->where('user_id', $client->id)
            ->where('title', 'Night Plan')
            ->firstOrFail();

        $response = $this->actingAs($client)->post(route('client.completions.store', $item), [
            'date' => now()->toDateString(),
            'category' => 'night',
        ]);

        $response->assertRedirect(route('client.dashboard', [
            'date' => now()->toDateString(),
            'category' => 'night',
        ]));
    }

    public function test_client_can_mark_a_completed_item_back_to_pending(): void
    {
        $this->seed();

        $client = User::query()->where('email', 'moemortezaei@gmail.com')->firstOrFail();
        $completion = ProgramCompletion::query()
            ->where('user_id', $client->id)
            ->firstOrFail();

        $response = $this->actingAs($client)->delete(
            route('client.completions.destroy', $completion->programItem),
            ['date' => $completion->completion_date->toDateString()],
        );

        $response->assertRedirect(route('client.dashboard', ['date' => $completion->completion_date->toDateString()]));

        $this->assertDatabaseMissing('program_completions', [
            'id' => $completion->id,
        ]);
    }
}
