<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    public $incrementing = false;

    protected $fillable = ['id', 'content'];

    protected function casts(): array
    {
        return ['content' => 'array'];
    }

    public static function current(): array
    {
        $content = array_replace(config('catalogue.defaults'), self::find(1)?->content ?? []);
        $content['socials'] = array_filter([
            'Instagram' => $content['instagram'],
            'Facebook' => $content['facebook'],
            'TikTok' => $content['tiktok'],
        ]);

        return $content;
    }

    public static function dial(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number);

        return str_starts_with($digits, '0') ? '234'.substr($digits, 1) : $digits;
    }

    public static function whatsapp(string $number, string $message = 'Hello Ad_Dreamwear.Co, I have a catalogue enquiry.'): string
    {
        return 'https://wa.me/'.self::dial($number).'?text='.rawurlencode($message);
    }

    public static function bannerUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'collection/')
            ? asset($path)
            : route('media.banner', basename($path));
    }
}
