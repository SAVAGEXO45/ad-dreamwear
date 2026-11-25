<?php

namespace App\Http\Requests;

use App\Services\CatalogueMedia;
use Illuminate\Foundation\Http\FormRequest;

class SaveSite extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        $phone = ['string', 'regex:/^(?:0|\+?234)(?:[ ()-]*[0-9]){10}$/'];
        $rules = [
            'hero_description' => ['required', 'string', 'max:350'],
            'intro_description' => ['required', 'string', 'max:1000'],
            'about_text' => ['required', 'string', 'max:4000'],
            'contact_text' => ['required', 'string', 'max:1000'],
            'phone_primary' => ['required', ...$phone],
            'phone_secondary' => ['nullable', ...$phone],
            'whatsapp' => ['required', ...$phone],
            'email' => ['required', 'email', 'max:255'],
            'announcement' => ['nullable', 'string', 'max:160'],
            'footer_text' => ['nullable', 'string', 'max:320'],
            'banner_desktop' => ['nullable', ...CatalogueMedia::rules()],
            'banner_mobile' => ['nullable', ...CatalogueMedia::rules()],
            'remove_banner_desktop' => ['sometimes', 'boolean'],
            'remove_banner_mobile' => ['sometimes', 'boolean'],
        ];

        foreach (['hero', 'intro', 'featured', 'arrivals', 'collections', 'about', 'contact'] as $section) {
            $rules[$section.'_heading'] = ['required', 'string', 'max:120'];
        }
        foreach (['instagram', 'facebook', 'tiktok'] as $network) {
            $rules[$network] = ['nullable', 'url:https', 'max:500'];
        }

        return $rules;
    }
}
