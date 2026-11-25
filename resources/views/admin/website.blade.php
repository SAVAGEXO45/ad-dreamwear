@extends('layouts.admin')
@section('title', 'Website content')
@section('content')
    <div class="admin-title">
        <div><h1>Website content</h1><p>Changes appear on the public website when you save.</p></div>
        <a class="button button-outline" href="{{ route('home') }}">View website</a>
    </div>
    <form class="form-stack" method="post" enctype="multipart/form-data" action="{{ route('admin.website.update') }}">
        @csrf @method('put')
        <div class="admin-form-grid">
            <div class="form-stack">
                <section class="panel form-stack">
                    <h2>Homepage</h2>
                    <x-admin-input name="hero_heading" label="Hero heading" :value="$site['hero_heading']" maxlength="120" required />
                    <x-admin-input name="hero_description" label="Hero description" type="textarea" :value="$site['hero_description']" maxlength="350" required />
                    <x-admin-input name="intro_heading" label="Introduction heading" :value="$site['intro_heading']" maxlength="120" required />
                    <x-admin-input name="intro_description" label="Introduction description" type="textarea" :value="$site['intro_description']" maxlength="1000" required />
                    @foreach(['featured' => 'Featured dresses', 'arrivals' => 'New Arrivals', 'collections' => 'Collections'] as $section => $label)
                        <x-admin-input :name="$section.'_heading'" :label="$label.' heading'" :value="$site[$section.'_heading']" maxlength="120" required />
                    @endforeach
                </section>
                <section class="panel form-stack">
                    <h2>Homepage banners</h2>
                    @foreach(['banner_desktop' => 'Main banner', 'banner_mobile' => 'Mobile banner'] as $field => $label)
                        <x-admin-input :name="$field" :label="$label" type="file" accept="image/jpeg,image/png,image/webp" :hint="$field === 'banner_mobile' ? 'Optional. Used on screens up to 700 pixels wide.' : 'JPEG, PNG or WebP up to 6 MB.'" />
                        @if($site[$field])
                            <img class="admin-banner" src="{{ \App\Models\SiteSetting::bannerUrl($site[$field]) }}" alt="Current {{ strtolower($label) }}" loading="lazy">
                            <label class="check-label"><input type="checkbox" name="remove_{{ $field }}" value="1" @checked(old('remove_'.$field))> Remove {{ strtolower($label) }}</label>
                        @endif
                    @endforeach
                </section>
            </div>
            <div class="form-stack">
                <section class="panel form-stack">
                    <h2>About and contact</h2>
                    <x-admin-input name="about_heading" label="About heading" :value="$site['about_heading']" maxlength="120" required />
                    <x-admin-input name="about_text" label="About text" type="textarea" :value="$site['about_text']" maxlength="4000" required />
                    <x-admin-input name="contact_heading" label="Contact heading" :value="$site['contact_heading']" maxlength="120" required />
                    <x-admin-input name="contact_text" label="Contact text" type="textarea" :value="$site['contact_text']" maxlength="1000" required />
                    <x-admin-input name="phone_primary" label="Primary phone" type="tel" :value="$site['phone_primary']" required />
                    <x-admin-input name="phone_secondary" label="Second phone" type="tel" :value="$site['phone_secondary']" />
                    <x-admin-input name="whatsapp" label="WhatsApp enquiry number" type="tel" :value="$site['whatsapp']" required />
                    <x-admin-input name="email" label="Public email address" type="email" :value="$site['email']" maxlength="255" required />
                </section>
                <section class="panel form-stack">
                    <h2>Footer and announcement</h2>
                    <x-admin-input name="announcement" label="Announcement" :value="$site['announcement']" maxlength="160" hint="Clear this field to hide the bar above the navigation." />
                    <x-admin-input name="footer_text" label="Footer text" type="textarea" :value="$site['footer_text']" maxlength="320" />
                    @foreach(['Instagram', 'Facebook', 'TikTok'] as $network)
                        <x-admin-input :name="strtolower($network)" :label="$network.' URL'" type="url" :value="$site[strtolower($network)]" maxlength="500" placeholder="https://" />
                    @endforeach
                </section>
            </div>
        </div>
        <div><button class="button button-primary" type="submit">Save website changes</button></div>
    </form>
@endsection
