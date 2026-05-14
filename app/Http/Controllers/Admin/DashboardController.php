<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DailyProgramService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DailyProgramService $dailyProgramService): View
    {
        $date = $dailyProgramService->resolveDate($request->string('date')->toString());

        $clients = User::query()
            ->clients()
            ->orderBy('full_name')
            ->get()
            ->map(function (User $client) use ($dailyProgramService, $date): User {
                $summary = $dailyProgramService->summary(
                    $dailyProgramService->itemsForDate($client, $date)
                );

                $client->setAttribute('daily_summary', $summary);

                return $client;
            });

        $totals = [
            'clients' => $clients->count(),
            'tasks' => $clients->sum(fn (User $client) => $client->daily_summary['total']),
            'completed' => $clients->sum(fn (User $client) => $client->daily_summary['completed']),
            'pending' => $clients->sum(fn (User $client) => $client->daily_summary['pending']),
            'fully_done' => $clients
                ->filter(fn (User $client) => $client->daily_summary['total'] > 0 && $client->daily_summary['pending'] === 0)
                ->count(),
        ];

        return view('admin.dashboard', [
            'date' => $date,
            'clients' => $clients,
            'totals' => $totals,
        ]);
    }
}
