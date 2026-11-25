<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CatalogueMedia
{
    public static function rules(): array
    {
        return ['file', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:6144', 'dimensions:max_width=4096,max_height=4096'];
    }

    public static function bundled(string $path): bool
    {
        return str_starts_with($path, 'collection/');
    }

    public function store(UploadedFile $file, string $field): string
    {
        $bytes = file_get_contents($file->getPathname());
        $image = $bytes === false ? false : @imagecreatefromstring($bytes);
        if ($image === false) {
            throw ValidationException::withMessages([$field => 'This image could not be read. Try exporting it as JPEG, PNG or WebP.']);
        }

        if ($file->getMimeType() === 'image/jpeg' && function_exists('exif_read_data')) {
            $orientation = @exif_read_data($file->getPathname())['Orientation'] ?? 1;
            if (in_array($orientation, [2, 4, 5, 7], true)) {
                imageflip($image, IMG_FLIP_HORIZONTAL);
            }
            $degrees = match ($orientation) {
                3, 4 => 180,
                5, 8 => 90,
                6, 7 => -90,
                default => 0,
            };
            if ($degrees !== 0) {
                $image = imagerotate($image, $degrees, 0);
            }
        }

        if (max(imagesx($image), imagesy($image)) > 2000) {
            $ratio = 2000 / max(imagesx($image), imagesy($image));
            $image = imagescale($image, max(1, (int) (imagesx($image) * $ratio)), max(1, (int) (imagesy($image) * $ratio)));
        }

        imagepalettetotruecolor($image);
        imagesavealpha($image, true);

        ob_start();
        try {
            if (! imagewebp($image, null, 87)) {
                throw new RuntimeException('Unable to encode the image.');
            }
            $encoded = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        $path = 'catalogue/'.Str::uuid().'.webp';
        if (! $encoded || ! Storage::disk('local')->put($path, $encoded)) {
            throw new RuntimeException('Unable to save the image.');
        }

        return $path;
    }

    public function discard(array $paths): void
    {
        foreach (array_filter($paths) as $path) {
            if (! self::bundled($path)) {
                Storage::disk('local')->delete($path);
            }
        }
    }
}
