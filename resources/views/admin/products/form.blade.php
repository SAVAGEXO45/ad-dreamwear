@extends('layouts.admin')
@section('title', $product->exists ? 'Edit dress' : 'Add dress')
@section('content')
    <div class="admin-title">
        <div>
            <a class="text-link" href="{{ route('admin.dashboard', ['collection' => $product->category_id]) }}">Back to catalogue</a>
            <h1>{{ $product->exists ? 'Edit dress' : 'Add dress' }}</h1>
        </div>
        @if($product->exists && $product->published)
            <a class="button button-outline" href="{{ route('dresses.show', $product->slug) }}">View dress</a>
        @endif
    </div>
    @if($categories->isEmpty())
        <div class="notice">Create a collection before adding a dress. <a class="text-link" href="{{ route('admin.categories.create') }}">Create collection</a></div>
    @else
        <form method="post" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="form-stack">
            @csrf
            @if($product->exists) @method('put') @endif
            <div class="admin-form-grid">
                <section class="panel form-stack">
                    <h2>Dress details</h2>
                    <x-admin-input name="name" label="Product name" :value="$product->name" maxlength="120" required />
                    <div class="field">
                        <label for="category_id">Collection</label>
                        <select name="category_id" id="category_id" required>
                            <option value="">Select a collection</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-admin-input name="description" label="Description (optional)" type="textarea" :value="$product->description" maxlength="5000" hint="For your records." />
                    <x-admin-input name="sizes" label="Available sizes" :value="implode(', ', $product->sizes ?? [])" maxlength="320" hint="Separate sizes with commas, for example S, M, L or UK 10, UK 12. Leave blank if sizes need confirmation." />
                    <div class="field-pair">
                        <x-admin-input name="price" label="Price (₦)" type="number" :value="$product->price ?? '6000.00'" step="0.01" min="0.01" max="99999999.99" required />
                        <x-admin-input name="sort_order" label="Display position" type="number" :value="$product->sort_order" min="0" max="999999" hint="Lower numbers appear first." required />
                    </div>
                    <fieldset class="check-group">
                        <legend>Visibility and availability</legend>
                        @foreach(['published' => 'Published on the website', 'available' => 'Available', 'featured' => 'Show in Featured', 'new_arrival' => 'Show in New Arrivals'] as $flag => $label)
                            <input type="hidden" name="{{ $flag }}" value="0">
                            <label><input type="checkbox" name="{{ $flag }}" value="1" @checked(old($flag, $product->$flag))> {{ $label }}</label>
                        @endforeach
                    </fieldset>
                </section>
                <section class="panel form-stack">
                    <h2>Photographs</h2>
                    @if($product->exists)
                        <img class="admin-main-photo" src="{{ $product->imageUrl() }}" alt="Current main photograph">
                    @endif
                    <x-admin-input name="main_image" :label="$product->exists ? 'Replace main photograph' : 'Main photograph'" type="file" :required="!$product->exists" accept="image/jpeg,image/png,image/webp" hint="JPEG, PNG or WebP. Up to 6 MB per photo." />
                    <div class="field">
                        <label for="photos">Add gallery photographs</label>
                        <input type="file" id="photos" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" aria-describedby="photos-hint">
                        <p class="field-hint" id="photos-hint">Select multiple photos at once. Up to 12 gallery photos per dress.</p>
                    </div>
                    @if($product->exists && $product->photos->isNotEmpty())
                        <div class="admin-thumbs">
                            @foreach($product->photos as $photo)
                                <label>
                                    <img src="{{ $photo->imageUrl() }}" alt="Gallery photo {{ $loop->iteration }}" loading="lazy">
                                    <span><input type="checkbox" name="remove_photos[]" value="{{ $photo->id }}" @checked(in_array($photo->id, old('remove_photos', [])))> Remove</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
            <div><button class="button button-primary" type="submit">Save dress</button></div>
        </form>
        @if($product->exists)
            <form class="danger-zone" method="post" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Permanently delete this dress and its uploaded photos?">
                @csrf @method('delete')
                <button class="text-link danger-link" type="submit">Delete dress</button>
            </form>
        @endif
    @endif
@endsection
