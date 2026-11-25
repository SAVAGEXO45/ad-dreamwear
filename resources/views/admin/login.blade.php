@extends('layouts.admin')
@section('title', 'Owner sign in')
@section('content')
    <form class="panel login-panel form-stack" action="{{ route('login.store') }}" method="post">
        @csrf
        <h1>Owner sign in</h1>
        <x-admin-input name="email" label="Email address" type="email" autocomplete="username" maxlength="255" required />
        <x-admin-input name="password" label="Password" type="password" autocomplete="current-password" maxlength="255" required />
        <button class="button button-primary" type="submit">Sign in</button>
    </form>
@endsection
