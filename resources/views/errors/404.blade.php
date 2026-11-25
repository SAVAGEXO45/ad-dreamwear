@extends('layouts.admin')
@section('title', 'Page unavailable')
@section('content')
    <section class="panel form-stack narrow-form">
        <h1>Page unavailable</h1>
        <p>The page may have moved or the dress may be hidden from the catalogue.</p>
        <a class="text-link" href="{{ route('catalogue') }}">Browse dresses</a>
    </section>
@endsection
