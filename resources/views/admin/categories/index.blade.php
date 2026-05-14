@extends('layouts.app')

@section('content')
    <section class="surface-card hero-panel p-4 p-lg-5 mb-4">
        <p class="text-uppercase small fw-semibold mb-2 muted-copy">Dynamic categories</p>
        <h1 class="display-6 mb-2">Manage client categories</h1>
        <p class="muted-copy mb-0">
            Start with morning, evening, and night, but add, rename, reorder, or disable categories whenever the business changes.
        </p>
    </section>

    <section class="row g-4">
        <div class="col-lg-4">
            <div class="surface-card p-4">
                <h2 class="section-title mb-3">Add category</h2>
                <form method="POST" action="{{ route('admin.categories.store') }}" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label" for="name">Name</label>
                        <input id="name" type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="sort_order">Sort order</label>
                        <input id="sort_order" type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $categories->count() + 1) }}" min="0" required>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">Active category</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Add category</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="surface-card table-shell">
                <div class="px-4 pt-4">
                    <h2 class="section-title mb-1">Current categories</h2>
                    <p class="muted-copy mb-0">Updating a category here changes what admins can assign in daily programs.</p>
                </div>

                <div class="table-responsive px-4 pb-4 pt-3">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Order</th>
                                <th>Active</th>
                                <th>Assigned items</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td>
                                        <input type="text" name="name" class="form-control" value="{{ $category->name }}" form="update-category-{{ $category->id }}" required>
                                    </td>
                                    <td>
                                        <input type="number" name="sort_order" class="form-control" value="{{ $category->sort_order }}" form="update-category-{{ $category->id }}" min="0" required>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input type="hidden" name="is_active" value="0" form="update-category-{{ $category->id }}">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="category_{{ $category->id }}" form="update-category-{{ $category->id }}" @checked($category->is_active)>
                                            <label class="form-check-label" for="category_{{ $category->id }}">
                                                Active
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="small muted-copy">{{ $category->program_items_count }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="submit" class="btn btn-outline-primary btn-sm" form="update-category-{{ $category->id }}">Save</button>
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                            </form>
                                        </div>
                                        <form id="update-category-{{ $category->id }}" method="POST" action="{{ route('admin.categories.update', $category) }}">
                                            @csrf
                                            @method('PUT')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
