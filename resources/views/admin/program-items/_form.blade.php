@php
    $scheduledDate = old('scheduled_date', optional($programItem->scheduled_date)->format('Y-m-d'));
    $startsOn = old('starts_on', optional($programItem->starts_on)->format('Y-m-d'));
    $endsOn = old('ends_on', optional($programItem->ends_on)->format('Y-m-d'));
@endphp

<form method="POST" action="{{ $action }}" class="row g-3" data-program-item-form>
    @csrf
    @if ($httpMethod !== 'POST')
        @method($httpMethod)
    @endif

    <input type="hidden" name="return_date" value="{{ $returnDate }}">

    <div class="col-12">
        <label class="form-label" for="kind">Item type</label>
        <select id="kind" name="kind" class="form-select" required>
            <option value="category_program" @selected(old('kind', $programItem->kind) === 'category_program')>Category program</option>
            <option value="routine" @selected(old('kind', $programItem->kind) === 'routine')>Routine</option>
        </select>
    </div>

    <div class="col-12">
        <label class="form-label" for="title">Title</label>
        <input id="title" type="text" name="title" class="form-control" value="{{ old('title', $programItem->title) }}" required>
    </div>

    <div class="col-12">
        <label class="form-label" for="category_id">Category</label>
        <select id="category_id" name="category_id" class="form-select" required>
            <option value="">Select category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $programItem->category_id) === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Routines and category programs both use categories like Morning, Evening, and Night.</div>
    </div>

    <div class="col-12">
        <label class="form-label" for="recurrence_type">Schedule</label>
        <select id="recurrence_type" name="recurrence_type" class="form-select" required>
            <option value="once" @selected(old('recurrence_type', $programItem->recurrence_type) === 'once')>Only one day</option>
            <option value="daily" @selected(old('recurrence_type', $programItem->recurrence_type) === 'daily')>Every day</option>
        </select>
    </div>

    <div class="col-12" data-schedule-field="once">
        <label class="form-label" for="scheduled_date">Date for one-day item</label>
        <input id="scheduled_date" type="date" name="scheduled_date" class="form-control" value="{{ $scheduledDate }}">
    </div>

    <div class="col-12" data-schedule-field="daily">
        <label class="form-label" for="starts_on">Start date for daily routine</label>
        <input id="starts_on" type="date" name="starts_on" class="form-control" value="{{ $startsOn }}">
    </div>

    <div class="col-12" data-schedule-field="daily">
        <label class="form-label" for="ends_on">End date</label>
        <input id="ends_on" type="date" name="ends_on" class="form-control" value="{{ $endsOn }}">
    </div>

    <div class="col-12">
        <label class="form-label" for="content_type">Content type</label>
        <select id="content_type" name="content_type" class="form-select" required>
            <option value="text" @selected(old('content_type', $programItem->content_type) === 'text')>Text</option>
            <option value="video" @selected(old('content_type', $programItem->content_type) === 'video')>Video</option>
        </select>
    </div>

    <div class="col-12" data-content-field="video">
        <label class="form-label" for="video_url">Video URL</label>
        <input id="video_url" type="url" name="video_url" class="form-control" value="{{ old('video_url', $programItem->video_url) }}" placeholder="https://...">
    </div>

    <div class="col-12" data-content-field="text">
        <label class="form-label" for="content_body">Text instructions / notes</label>
        <textarea id="content_body" name="content_body" class="form-control" rows="5">{{ old('content_body', $programItem->content_body) }}</textarea>
    </div>

    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_item" @checked(old('is_active', $programItem->is_active ?? true))>
            <label class="form-check-label" for="is_active_item">
                Active item
            </label>
        </div>
    </div>

    <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        <a href="{{ route('admin.clients.show', ['client' => $client, 'date' => $returnDate]) }}" class="btn btn-outline-primary">Cancel</a>
    </div>
</form>
