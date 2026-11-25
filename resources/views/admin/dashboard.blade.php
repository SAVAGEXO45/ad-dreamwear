@extends('layouts.admin')
@section('title', 'Catalogue')
@section('content')
    <div class="admin-title">
        <h1>Catalogue</h1>
    </div>

    <div class="form-stack">
        @forelse($groups as $group)
            @include('admin.products.index', $group)
        @empty
            <div class="panel">
                <p class="empty-text">Add a collection to start organising your catalogue.</p>
                <a class="text-link" href="{{ route('admin.categories.create') }}">Add collection</a>
            </div>
        @endforelse

        <details class="panel admin-list">
            <summary class="text-link">Catalogue activity</summary>
            <div class="stats-grid">
                @foreach($counts as $label => $count)
                    <div class="panel stat"><span>{{ $label }}</span><strong>{{ $count }}</strong></div>
                @endforeach
            </div>
            <h2>Recent uploads</h2>
            @forelse($recent as $upload)
                <a class="admin-item" href="{{ route('admin.products.edit', $upload['product']) }}">
                    <img src="{{ $upload['url'] }}" alt="" loading="lazy">
                    <div><strong>{{ $upload['product']->name }}</strong><small>{{ $upload['kind'] }} · {{ $upload['date']->format('j M Y') }}</small></div>
                    <span class="text-link">Edit dress</span>
                </a>
            @empty
                <p class="empty-text">Dress photographs will appear here after upload.</p>
            @endforelse
        </details>
    </div>
@endsection
