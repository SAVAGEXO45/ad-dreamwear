@extends('layouts.catalogue')
@section('title', $product->name.' | Ad_Dreamwear.Co')
@section('content')
    <article class="shell product-page">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ route('catalogue') }}">Collection</a><span aria-hidden="true">/</span>
            <a href="{{ route('catalogue', ['collection' => $product->category->slug]) }}">{{ $product->category->name }}</a>
        </nav>
        <div class="product-layout">
            <div class="product-photos" data-gallery>
                <div class="dress-photo detail-photo">
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" fetchpriority="high" data-gallery-main>
                </div>
                @if($product->photos->isNotEmpty())
                    <div class="thumbs" aria-label="Product photographs">
                        <button type="button" data-photo="{{ $product->imageUrl() }}" data-alt="{{ $product->name }}" aria-pressed="true" aria-label="Show main photograph">
                            <img src="{{ $product->imageUrl() }}" alt="" loading="lazy">
                        </button>
                        @foreach($product->photos as $photo)
                            <button type="button" data-photo="{{ $photo->imageUrl() }}" data-alt="{{ $product->name }} (photo {{ $loop->iteration + 1 }})" aria-pressed="false" aria-label="Show photograph {{ $loop->iteration + 1 }}">
                                <img src="{{ $photo->imageUrl() }}" alt="" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                    <noscript><div class="thumbs">
                        @foreach($product->photos as $photo)
                            <a href="{{ $photo->imageUrl() }}">View photograph {{ $loop->iteration + 1 }}</a>
                        @endforeach
                    </div></noscript>
                @endif
            </div>
            <div class="product-info">
                <p class="eyebrow">{{ $product->category->name }}</p>
                <h1>{{ $product->name }}</h1>
                <p class="product-price">{{ $product->displayPrice() }}</p>
                <p class="availability">{{ $product->available ? 'Available' : 'Currently unavailable' }}</p>
                <p class="product-description">{{ $product->sizes ? 'Sizes: '.implode(', ', $product->sizes) : 'Enquire about available sizes' }}</p>
                <a class="button button-primary" href="{{ \App\Models\SiteSetting::whatsapp($site['whatsapp'], 'Hello Ad_Dreamwear.Co, I’d like to enquire about the '.$product->name.'.') }}">Enquire on WhatsApp</a>
            </div>
        </div>
    </article>
    @if($related->isNotEmpty())
        <section class="shell catalogue-section related">
            <div class="section-heading"><h2>More from {{ $product->category->name }}</h2></div>
            <div class="dresses-grid">@foreach($related as $dress)<x-dress-card :product="$dress" />@endforeach</div>
        </section>
    @endif
@endsection
