@extends('layouts.admin')
@section('title', 'Collections')
@section('content')
    <div class="admin-title">
        <div><h1>Collections</h1><p>Group your dresses for visitors to browse.</p></div>
        <a class="button button-primary" href="{{ route('admin.categories.create') }}">Add collection</a>
    </div>
    <div class="panel admin-list">
        @forelse($categories as $category)
            <a class="admin-item no-thumb" href="{{ route('admin.categories.edit', $category) }}">
                <div class="collection-summary">
                    <x-collection-icon :category="$category" />
                    <div><strong>{{ $category->name }}</strong><small>{{ $category->products_count }} {{ \Illuminate\Support\Str::plural('style', $category->products_count) }}</small></div>
                </div>
                <span class="text-link">Edit collection</span>
            </a>
        @empty
            <p class="empty-text">No collections have been created.</p>
        @endforelse
    </div>
    <div class="pagination">{{ $categories->links() }}</div>
@endsection
