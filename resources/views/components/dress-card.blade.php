@props(['product', 'showArrival' => false])
<article class="dress-card">
    <a href="{{ route('dresses.show', $product->slug) }}">
        <div class="dress-photo">
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy" decoding="async">
            @if(!$product->available)
                <span class="photo-label">Unavailable</span>
            @elseif($showArrival && $product->new_arrival)
                <span class="photo-label">New arrival</span>
            @endif
        </div>
        <div class="dress-caption">
            <h3>{{ $product->name }}</h3>
            <p>{{ $product->displayPrice() }}</p>
        </div>
        @if($product->sizes)
            <p class="field-hint">Sizes {{ implode(', ', $product->sizes) }}</p>
        @endif
    </a>
</article>
