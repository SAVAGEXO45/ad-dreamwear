@extends('layouts.admin')
@section('title', $category->exists ? 'Edit collection' : 'Add collection')
@section('content')
    <div class="admin-title">
        <div><a class="text-link" href="{{ route('admin.categories.index') }}">All collections</a><h1>{{ $category->exists ? 'Edit collection' : 'Add collection' }}</h1></div>
    </div>
    <form class="panel form-stack narrow-form" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="post">
        @csrf
        @if($category->exists) @method('put') @endif
        <x-admin-input name="name" label="Collection name" :value="$category->name" maxlength="100" required />
        <x-admin-input name="description" label="Description" :value="$category->description" type="textarea" maxlength="1000" />
        <div><button type="submit" class="button button-primary">Save collection</button></div>
    </form>
    @if($category->exists)
        <form class="danger-zone" action="{{ route('admin.categories.destroy', $category) }}" method="post" data-confirm="Permanently delete this collection?">
            @csrf @method('delete')
            <button class="text-link danger-link" type="submit">Delete collection</button>
        </form>
    @endif
@endsection
