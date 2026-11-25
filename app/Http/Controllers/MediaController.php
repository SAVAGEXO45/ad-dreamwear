<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function product(Request $request, Product $product, string $file)
    {
        abort_unless($product->published || $request->user()?->is_admin, 404);
        $path = 'catalogue/'.$file;
        abort_unless($product->main_image === $path || $product->photos()->where('path', $path)->exists(), 404);

        return $this->image($path);
    }

    public function banner(string $file)
    {
        $site = SiteSetting::current();
        $path = 'catalogue/'.$file;
        abort_unless(in_array($path, [$site['banner_desktop'], $site['banner_mobile']], true), 404);

        return $this->image($path);
    }

    private function image(string $path)
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, basename($path), [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
