@props(['category'])
@if($category->coverProduct)
    <img class="collection-icon" src="{{ $category->coverProduct->imageUrl() }}" alt="" width="60" height="80" loading="lazy" decoding="async">
@endif
