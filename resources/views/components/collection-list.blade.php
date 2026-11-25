@props(['categories'])
<div class="collection-list">
    @forelse($categories as $category)
        <a href="{{ route('catalogue', ['collection' => $category->slug]) }}" class="collection-row">
            <div class="collection-summary">
                <x-collection-icon :category="$category" />
                <div>
                    <h3>{{ $category->name }}</h3>
                    @if($category->description)<p>{{ $category->description }}</p>@endif
                </div>
            </div>
            <span>{{ $category->products_count }} {{ \Illuminate\Support\Str::plural('style', $category->products_count) }}</span>
        </a>
    @empty
        <p class="empty-text">Collections will appear when products are published.</p>
    @endforelse
</div>
