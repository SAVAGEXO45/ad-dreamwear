<!doctype html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Ad_Dreamwear.Co | Fashion Collections')</title>
    <meta name="description" content="@yield('description', 'Discover fashion collections from Ad_Dreamwear.Co.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    @if($site['announcement'])
        <div class="announcement">{{ $site['announcement'] }}</div>
    @endif
    <header class="site-header">
        <div class="shell nav-inner">
            <a class="wordmark" href="{{ route('home') }}">Ad_Dreamwear<span>.Co</span></a>
            <nav class="desktop-nav" aria-label="Main navigation">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
                <x-collection-nav :categories="$categories" />
                <a href="{{ route('catalogue', ['view' => 'new']) }}" @if(request()->routeIs('catalogue') && !request('collection') && request('view') === 'new') aria-current="page" @endif>New Arrivals</a>
                <a href="{{ route('home') }}#about">About</a>
            </nav>
            <a class="nav-contact" href="{{ route('home') }}#contact">Contact</a>
            <details class="mobile-nav">
                <summary>Menu <span class="menu-lines" aria-hidden="true"></span></summary>
                <nav aria-label="Mobile navigation">
                    <a href="{{ route('home') }}">Home</a>
                    <x-collection-nav :categories="$categories" />
                    <a href="{{ route('catalogue', ['view' => 'new']) }}">New Arrivals</a>
                    <a href="{{ route('home') }}#about">About</a>
                    <a href="{{ route('home') }}#contact">Contact</a>
                </nav>
            </details>
        </div>
    </header>
    <main id="main">@yield('content')</main>
    <footer class="site-footer">
        <div class="shell footer-inner">
            <div>
                <a class="wordmark" href="{{ route('home') }}">Ad_Dreamwear<span>.Co</span></a>
                @if($site['footer_text'])<p>{{ $site['footer_text'] }}</p>@endif
            </div>
            <nav aria-label="Footer navigation">
                <a href="{{ route('catalogue') }}">Collections</a>
                <a href="{{ route('home') }}#about">About</a>
                <a href="{{ route('home') }}#contact">Contact</a>
                @foreach($site['socials'] as $label => $url)
                    <a href="{{ $url }}">{{ $label }}</a>
                @endforeach
            </nav>
        </div>
        <div class="shell footer-bottom"><small>© {{ now()->year }} Ad_Dreamwear.Co</small></div>
    </footer>
</body>
</html>
