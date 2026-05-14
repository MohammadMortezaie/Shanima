@extends('layouts.app')

@section('content')
    <section class="surface-card hero-panel p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-end">
            <div class="col-lg-7">
                <p class="text-uppercase small fw-semibold mb-2 muted-copy">Client dashboard</p>
                <h1 class="display-6 mb-2">My plan for {{ $date->format('M d, Y') }}</h1>
                <p class="muted-copy mb-0">
                    Your plan is shown in order from top to bottom so it is clear what comes first and what comes next.
                </p>
            </div>
            <div class="col-lg-5">
                <form method="GET" action="{{ route('client.dashboard') }}" class="surface-card-tight p-3">
                    @if ($selectedCategorySlug)
                        <input type="hidden" name="category" value="{{ $selectedCategorySlug }}">
                    @endif

                    <label class="form-label fw-semibold" for="date">View a date</label>
                    <div class="d-flex gap-2">
                        <input id="date" type="date" name="date" value="{{ $date->toDateString() }}" class="form-control">
                        <button type="submit" class="btn btn-primary">Go</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="stats-grid mb-4">
        <div>
            <div class="surface-card p-4 stat-card">
                <div class="stat-label mb-3">Tasks today</div>
                <div class="stat-value">{{ $summary['total'] }}</div>
                <div class="muted-copy mt-2">Programs + routines</div>
            </div>
        </div>
        <div>
            <div class="surface-card p-4 stat-card">
                <div class="stat-label mb-3">Completed</div>
                <div class="stat-value">{{ $summary['completed'] }}</div>
                <div class="muted-copy mt-2">Finished so far</div>
            </div>
        </div>
        <div>
            <div class="surface-card p-4 stat-card">
                <div class="stat-label mb-3">Pending</div>
                <div class="stat-value">{{ $summary['pending'] }}</div>
                <div class="muted-copy mt-2">Still left to do</div>
            </div>
        </div>
        <div>
            <div class="surface-card p-4 stat-card">
                <div class="stat-label mb-3">Progress</div>
                <div class="stat-value">{{ $summary['completion_rate'] }}%</div>
                <div class="muted-copy mt-2">
                    @if ($isFutureDate)
                        Future date view
                    @elseif ($summary['last_completed_at'])
                        Last done at {{ $summary['last_completed_at']->format('g:i A') }}
                    @else
                        Nothing completed yet
                    @endif
                </div>
            </div>
        </div>
    </section>
    <section>
        <div class="surface-card p-4 p-lg-5 mb-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-4">
                <div>
                    <h2 class="section-title mb-1">Category programs</h2>
                    <p class="muted-copy mb-0">These are the date-based programs under the same category system as the routines.</p>
                </div>

                @if ($selectedCategoryLabel)
                    <div class="soft-badge">Showing {{ $selectedCategoryLabel }}</div>
                @endif
            </div>

            <form method="GET" action="{{ route('client.dashboard') }}" class="category-filter-bar">
                <input type="hidden" name="date" value="{{ $date->toDateString() }}">

                <button type="submit" class="filter-pill {{ $selectedCategorySlug === '' ? 'active' : '' }}">
                    All categories
                </button>

                @foreach ($categoryFilters as $filter)
                    <button
                        type="submit"
                        name="category"
                        value="{{ $filter['slug'] }}"
                        class="filter-pill {{ $selectedCategorySlug === $filter['slug'] ? 'active' : '' }}"
                    >
                        {{ $filter['name'] }}
                        <span>{{ $filter['count'] }}</span>
                    </button>
                @endforeach
            </form>
        </div>

        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
            <div>
                <h2 class="section-title mb-1">Programs for {{ $date->format('M d, Y') }}</h2>
                <p class="muted-copy mb-0">This category-based section now appears first after the client dashboard header.</p>
            </div>
        </div>

        @forelse ($categoryGroups as $group)
            <section class="surface-card p-4 p-lg-5 mb-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="section-order-badge">{{ $loop->iteration }}</div>

                        <div>
                            <div class="soft-badge mb-2">{{ $group['label'] }}</div>
                            <h3 class="h4 mb-0">{{ $group['label'] }} programs</h3>
                        </div>
                    </div>

                    <div class="muted-copy small">{{ $group['items']->count() }} item(s)</div>
                </div>

                <div class="program-stack">
                    @foreach ($group['items'] as $item)
                        @php($completion = $item->getAttribute('completion_snapshot'))
                        <article class="program-card p-4">
                            <div class="program-order-shell">
                                <div class="program-order-badge">{{ $loop->iteration }}</div>

                                <div class="program-flow w-100">
                                    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3 mb-3">
                                        <div>
                                            <h4 class="h5 mb-1">{{ $item->title }}</h4>
                                            @if ($item->content_body)
                                                <p class="muted-copy mb-0">{{ $item->content_body }}</p>
                                            @endif
                                        </div>

                                        <span class="status-chip {{ $completion ? 'done' : 'pending' }}">
                                            {{ $completion ? 'Done' : 'Pending' }}
                                        </span>
                                    </div>

                                    @if ($item->content_type === 'video' && $item->video_url)
                                        @if ($item->video_embed_url)
                                            <div class="ratio ratio-16x9 mb-3">
                                                <iframe src="{{ $item->video_embed_url }}" allowfullscreen loading="lazy"></iframe>
                                            </div>
                                        @elseif ($item->video_provider === 'file')
                                            <div class="ratio ratio-16x9 mb-3">
                                                <video controls preload="metadata">
                                                    <source src="{{ $item->video_url }}">
                                                </video>
                                            </div>
                                        @else
                                            <a href="{{ $item->video_url }}" target="_blank" rel="noreferrer" class="btn btn-outline-primary btn-sm mb-3">Open video</a>
                                        @endif
                                    @endif

                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                                        <div class="small muted-copy">
                                            @if ($completion)
                                                Done at {{ $completion->completed_at->format('g:i A') }}
                                            @else
                                                Not completed yet
                                            @endif
                                        </div>

                                        @if (! $isFutureDate)
                                            @if ($completion)
                                                <form method="POST" action="{{ route('client.completions.destroy', $item) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                                    @if ($selectedCategorySlug)
                                                        <input type="hidden" name="category" value="{{ $selectedCategorySlug }}">
                                                    @endif
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">Mark pending</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('client.completions.store', $item) }}">
                                                    @csrf
                                                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                                    @if ($selectedCategorySlug)
                                                        <input type="hidden" name="category" value="{{ $selectedCategorySlug }}">
                                                    @endif
                                                    <button type="submit" class="btn btn-primary btn-sm">Mark done</button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="surface-card p-5 text-center muted-copy">
                @if ($selectedCategoryLabel)
                    No {{ strtolower($selectedCategoryLabel) }} programs are scheduled for this date.
                @else
                    No category programs are scheduled for this date.
                @endif
            </div>
        @endforelse
    </section>


    @if ($routines->isNotEmpty())
        <section class="mb-4">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h2 class="section-title mb-1 mt-1">Daily routines</h2>
                    <p class="muted-copy mb-0">Your repeating routine items now appear after the programs and progress sections.</p>
                </div>
                <div class="muted-copy small">{{ $routines->count() }} item(s)</div>
            </div>

            <div class="program-stack">
                @foreach ($routines as $item)
                    @php($completion = $item->getAttribute('completion_snapshot'))
                    <article class="program-card p-4">
                        <div class="program-order-shell">
                            <div class="program-order-badge">{{ $loop->iteration }}</div>

                            <div class="program-flow w-100">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3 mb-3">
                                    <div>
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <div class="soft-badge">Routine</div>

                                            @if ($item->category)
                                                <div class="soft-badge soft-badge-success">{{ $item->category->name }}</div>
                                            @endif
                                        </div>
                                        <h3 class="h5 mb-1">{{ $item->title }}</h3>
                                        @if ($item->content_body)
                                            <p class="muted-copy mb-0">{{ $item->content_body }}</p>
                                        @endif
                                    </div>

                                    <span class="status-chip {{ $completion ? 'done' : 'pending' }}">
                                        {{ $completion ? 'Done' : 'Pending' }}
                                    </span>
                                </div>

                                @if ($item->content_type === 'video' && $item->video_url)
                                    @if ($item->video_embed_url)
                                        <div class="ratio ratio-16x9 mb-3">
                                            <iframe src="{{ $item->video_embed_url }}" allowfullscreen loading="lazy"></iframe>
                                        </div>
                                    @elseif ($item->video_provider === 'file')
                                        <div class="ratio ratio-16x9 mb-3">
                                            <video controls preload="metadata">
                                                <source src="{{ $item->video_url }}">
                                            </video>
                                        </div>
                                    @else
                                        <a href="{{ $item->video_url }}" target="_blank" rel="noreferrer" class="btn btn-outline-primary btn-sm mb-3">Open video</a>
                                    @endif
                                @endif

                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                                    <div class="small muted-copy">
                                        @if ($completion)
                                            Done at {{ $completion->completed_at->format('g:i A') }}
                                        @else
                                            Not completed yet
                                        @endif
                                    </div>

                                    @if (! $isFutureDate)
                                        @if ($completion)
                                            <form method="POST" action="{{ route('client.completions.destroy', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                                @if ($selectedCategorySlug)
                                                    <input type="hidden" name="category" value="{{ $selectedCategorySlug }}">
                                                @endif
                                                <button type="submit" class="btn btn-outline-primary btn-sm">Mark pending</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('client.completions.store', $item) }}">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                                @if ($selectedCategorySlug)
                                                    <input type="hidden" name="category" value="{{ $selectedCategorySlug }}">
                                                @endif
                                                <button type="submit" class="btn btn-primary btn-sm">Mark done</button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
