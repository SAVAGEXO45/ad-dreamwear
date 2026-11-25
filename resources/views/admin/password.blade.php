@extends('layouts.admin')
@section('title', 'Change password')
@section('content')
    <div class="admin-title"><h1>Change password</h1></div>
    <form class="panel form-stack narrow-form" method="post" action="{{ route('admin.password.update') }}">
        @csrf
        @method('put')
        <x-admin-input name="current_password" label="Current password" type="password" autocomplete="current-password" maxlength="255" required />
        <x-admin-input name="password" label="New password" type="password" autocomplete="new-password" minlength="14" maxlength="72" hint="Use at least 14 characters, up to 72 bytes." required />
        <x-admin-input name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" maxlength="72" required />
        <div><button class="button button-primary" type="submit">Save new password</button></div>
    </form>
@endsection
