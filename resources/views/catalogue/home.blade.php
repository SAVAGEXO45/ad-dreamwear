@extends('layouts.catalogue')
@section('content')
    @php
        $desktopBanner = \App\Models\SiteSetting::bannerUrl($site['banner_desktop']);
        $mobileBanner = \App\Models\SiteSetting::bannerUrl($site['banner_mobile']);
    @endphp
    <section class="hero shell {{ $desktopBanner || $mobileBanner ? '' : 'hero-text-only' }}" aria-labelledby="hero-heading">
        <div class="hero-copy">
            <p class="eyebrow">{{ $categories->count() === 1 ? $categories->first()->name.' Collection' : 'Fashion collections' }}</p>
            <h1 id="hero-heading">
                @if($site['hero_heading'] === config('catalogue.defaults.hero_heading'))
                    Elegance Begins<br class="hero-break"> With What You <em>Wear.</em>
                @else
                    {{ $site['hero_heading'] }}
                @endif
            </h1>
            <p class="hero-description">{{ $site['hero_description'] }}</p>
            <a class="button button-primary" href="{{ route('catalogue') }}">{{ $categories->count() === 1 ? 'Browse '.$categories->first()->name : 'Browse collections' }}</a>
        </div>
        @if($desktopBanner || $mobileBanner)
            <picture class="hero-photo">
                @if($mobileBanner)<source media="(max-width: 700px)" srcset="{{ $mobileBanner }}">@endif
                <img src="{{ $desktopBanner ?? $mobileBanner }}" alt="{{ $site['banner_desktop'] === config('catalogue.defaults.banner_desktop') && !$mobileBanner ? 'Rose lounge dress with pink lace trim' : 'Ad_Dreamwear.Co collection banner' }}" fetchpriority="high">
            </picture>
        @endif
    </section>

    <section class="shell intro" aria-labelledby="intro-heading">
        <div>
            <p class="eyebrow">Ad_Dreamwear.Co</p>
            <h2 id="intro-heading">{{ $site['intro_heading'] }}</h2>
        </div>
        <p>{{ $site['intro_description'] }}</p>
    </section>

    <section class="shell catalogue-section" id="collections" aria-labelledby="collections-heading">
        <div class="section-heading"><h2 id="collections-heading">{{ $site['collections_heading'] }}</h2></div>
        <x-collection-list :categories="$categories" />
    </section>

    <section class="about-band" id="about" aria-labelledby="about-heading">
        <div class="shell about-inner">
            <div><p class="eyebrow">About us</p><h2 id="about-heading">{{ $site['about_heading'] }}</h2></div>
            <p class="preline">{{ $site['about_text'] }}</p>
        </div>
    </section>

    <section class="shell contact-section" id="contact" aria-labelledby="contact-heading">
        <div>
            <p class="eyebrow">Contact Ad_Dreamwear.Co</p>
            <h2 id="contact-heading">{{ $site['contact_heading'] }}</h2>
            <p class="preline">{{ $site['contact_text'] }}</p>
        </div>
        <div class="contact-links">
            <a href="tel:+{{ \App\Models\SiteSetting::dial($site['phone_primary']) }}">{{ $site['phone_primary'] }}</a>
            @if($site['phone_secondary'])
                <a href="tel:+{{ \App\Models\SiteSetting::dial($site['phone_secondary']) }}">{{ $site['phone_secondary'] }}</a>
            @endif
            <a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a>
            @if($site['instagram'])
                @php
                    $handle = trim((string) parse_url($site['instagram'], PHP_URL_PATH), '/');
                    $instagramLabel = preg_match('/^[a-z0-9_.]+$/iD', $handle) ? '@'.$handle : 'Instagram';
                @endphp
                <a href="{{ $site['instagram'] }}" style="display: inline-flex; align-items: center; gap: .5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false">
                        <rect x="3" y="3" width="18" height="18" rx="5"/>
                        <circle cx="12" cy="12" r="4"/>
                        <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
                    </svg>
                    <span>{{ $instagramLabel }}</span>
                </a>
            @endif
            <a class="button button-primary" href="{{ \App\Models\SiteSetting::whatsapp($site['whatsapp']) }}">WhatsApp enquiries</a>
        </div>
    </section>
@endsection
