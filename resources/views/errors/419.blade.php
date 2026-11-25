@extends('layouts.admin')
@section('title', 'Session expired')
@section('content')
    <section class="panel form-stack narrow-form">
        <h1>Session expired</h1>
        <p>Open the dashboard again before submitting the form.</p>
        <a class="text-link" href="{{ route('admin.dashboard') }}">Open dashboard</a>
    </section>
@endsection
