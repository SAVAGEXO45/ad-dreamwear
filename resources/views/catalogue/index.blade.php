@extends('layouts.catalogue')
@php
    $heading = $category?->name ?? match ($selection) {
        'new' => $site['arrivals_heading'],
        'featured' => $site['featured_heading'],
        default => $collection?->name ?? $site['collections_heading'],
    };
    $filters = $category ? ['collection' => $category->slug] : [];
@endphp
@section('title', $heading.' | Ad_Dreamwear.Co')
@section('content')
    <section class="page-heading">
        <div class="shell">
            <p class="eyebrow">The catalogue</p>
            <h1>{{ $heading }}</h1>
            @if(!$selection && $collection?->description)<p>{{ $collection->description }}</p>@endif
        </div>
    </section>

    @if($chooseCollection)
        <section class="shell catalogue-section" aria-label="Browse collections">
            <x-collection-list :categories="$categories" />
        </section>
    @else
        <div class="shell catalogue-results">
            <nav class="filter-list" aria-label="Filter styles">
                @if($categories->count() > 1)
                    <a href="{{ route('catalogue') }}">All collections</a>
                @endif
                @if($category || $categories->count() <= 1)
                    <a href="{{ route('catalogue', $filters) }}" @if(!$selection) aria-current="page" @endif>All styles</a>
                @endif
                <a href="{{ route('catalogue', $filters + ['view' => 'new']) }}" @if($selection === 'new') aria-current="page" @endif>New Arrivals</a>
                <a href="{{ route('catalogue', $filters + ['view' => 'featured']) }}" @if($selection === 'featured') aria-current="page" @endif>Featured</a>
            </nav>
            <div class="dresses-grid">
                @forelse($products as $product)
                    <x-dress-card :product="$product" :show-arrival="true" />
                @empty
                    <p class="empty-text">There are no styles in this selection yet.</p>
                @endforelse
            </div>
            <div class="pagination">{{ $products->links() }}</div>
        </div>
    @endif
@endsection
