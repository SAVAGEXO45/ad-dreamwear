<details class="panel admin-list collection-panel" data-collection-id="{{ $category->id }}" @if($open) open @endif>
    <summary class="collection-summary collection-toggle">
        <x-collection-icon :category="$category" />
        <span>
            <strong id="collection-title-{{ $category->id }}">{{ $category->name }}</strong>
            <small>{{ $category->products_count }} {{ \Illuminate\Support\Str::plural('item', $category->products_count) }}</small>
        </span>
    </summary>
    <section class="collection-items" id="collection-{{ $category->id }}" aria-labelledby="collection-title-{{ $category->id }}">
        <a class="button button-outline" href="{{ route('admin.products.create', ['category' => $category->id]) }}">Add item</a>
        @forelse($products as $product)
            <a class="admin-item" href="{{ route('admin.products.edit', $product) }}">
                <img src="{{ $product->imageUrl() }}" alt="" loading="lazy">
                <div>
                    <strong>{{ $product->name }}</strong>
                    <small>{{ $product->sizes ? 'Sizes: '.implode(', ', $product->sizes) : 'Sizes not set' }}</small>
                </div>
                <div class="admin-item-status">
                    <strong>{{ $product->displayPrice() }}</strong>
                    <small>{{ $product->published ? 'Published' : 'Draft' }} · {{ $product->available ? 'Available' : 'Unavailable' }}</small>
                    <span class="text-link">Edit</span>
                </div>
            </a>
        @empty
            <p class="empty-text">No items in this collection yet.</p>
        @endforelse
        @if($products->hasPages())
            <div class="pagination">{{ $products->links() }}</div>
        @endif
    </section>
</details>
