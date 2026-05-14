<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_can_open_the_admin_dashboard(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Daily client progress');
        $response->assertSee('Moe Mortezaei');
    }

    public function test_client_user_can_open_the_client_dashboard(): void
    {
        $this->seed();

        $client = User::query()->where('email', 'moemortezaei@gmail.com')->firstOrFail();

        $response = $this->actingAs($client)->get(route('client.dashboard'));

        $response->assertOk();
        $response->assertSee('My plan for');
        $response->assertSee('Skin Care Routine');
    }

    public function test_client_user_can_filter_the_dashboard_by_category(): void
    {
        $this->seed();

        $client = User::query()->where('email', 'moemortezaei@gmail.com')->firstOrFail();

        $response = $this->actingAs($client)->get(route('client.dashboard', [
            'date' => now()->toDateString(),
            'category' => 'morning',
        ]));

        $response->assertOk();
        $response->assertSee('Showing Morning');
        $response->assertSee('Morning Plan');
        $response->assertSee('Skin Care Routine');
        $response->assertDontSee('Evening Plan');
        $response->assertDontSee('Night Plan');
        $response->assertDontSee('Stretching');
    }
}
