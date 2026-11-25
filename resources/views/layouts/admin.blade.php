<!doctype html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') | Ad_Dreamwear.Co</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="admin-header">
        <div class="shell admin-top">
            <a class="wordmark" href="{{ route('home') }}">Ad_Dreamwear<span>.Co</span></a>
            @if(auth()->user()?->is_admin)
                <nav aria-label="Admin navigation">
                    <a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>Catalogue</a>
                    <a href="{{ route('admin.categories.index') }}" @if(request()->routeIs('admin.categories.*')) aria-current="page" @endif>Collections</a>
                    <a href="{{ route('admin.website.edit') }}" @if(request()->routeIs('admin.website.*')) aria-current="page" @endif>Website</a>
                    <a href="{{ route('admin.password.edit') }}" @if(request()->routeIs('admin.password.*')) aria-current="page" @endif>Change password</a>
                </nav>
                <form action="{{ route('admin.logout') }}" method="post">@csrf<button class="text-link" type="submit">Sign out</button></form>
            @else
                <a class="text-link" href="{{ route('home') }}">Back to catalogue</a>
            @endif
        </div>
    </header>
    <main class="shell admin-main" id="main">
        @if(session('status'))<p class="notice notice-success" role="status">{{ session('status') }}</p>@endif
        @if($errors->any())
            <div class="notice notice-error" role="alert">
                <strong>Check the following and try again.</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
