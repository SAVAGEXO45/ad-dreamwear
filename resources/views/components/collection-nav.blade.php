@props(['categories'])
@if($categories->isNotEmpty() && $categories->count() <= 2)
    @foreach($categories as $category)
        <a href="{{ route('catalogue', ['collection' => $category->slug]) }}" @if(request()->routeIs('catalogue') && request('collection') === $category->slug) aria-current="page" @endif>{{ $category->name }}</a>
    @endforeach
@else
    <a href="{{ route('catalogue') }}" @if(request()->routeIs('catalogue') && !request('collection') && !request('view')) aria-current="page" @endif>Collections</a>
@endif
