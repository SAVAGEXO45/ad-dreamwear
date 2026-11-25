<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveProduct;
use App\Models\Category;
use App\Models\Product;
use App\Services\CatalogueMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductController extends Controller
{
    public function __construct(private CatalogueMedia $media) {}

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('admin.dashboard', ['page' => $request->query('page')]);
    }

    public function create(Request $request)
    {
        $input = $request->validate(['category' => ['nullable', 'integer', 'exists:categories,id']]);

        return $this->form(new Product([
            'sort_order' => 100,
            'category_id' => $input['category'] ?? null,
        ]));
    }

    public function store(SaveProduct $request)
    {
        $product = $this->save($request, new Product);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Dress added.');
    }

    public function edit(Product $product)
    {
        return $this->form($product->load('photos'));
    }

    public function update(SaveProduct $request, Product $product)
    {
        $this->save($request, $product);

        return back()->with('status', 'Dress updated.');
    }

    public function destroy(Product $product)
    {
        $paths = DB::transaction(function () use ($product) {
            $record = Product::lockForUpdate()->findOrFail($product->id);
            $paths = $record->photos()->pluck('path')->push($record->main_image)->all();
            $record->delete();

            return $paths;
        });
        $this->media->discard($paths);

        return redirect()->route('admin.dashboard')->with('status', 'Dress deleted.');
    }

    private function form(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product, 'categories' => Category::orderBy('name')->get(),
        ]);
    }

    private function save(SaveProduct $request, Product $product): Product
    {
        $attributes = $request->safe()->except(['main_image', 'photos', 'remove_photos']);
        if (! $product->exists || array_key_exists('description', $attributes)) {
            $attributes['description'] ??= '';
        }
        if (array_key_exists('sizes', $attributes)) {
            $attributes['sizes'] = $request->sizes();
        }

        foreach (['available', 'published', 'new_arrival', 'featured'] as $flag) {
            $attributes[$flag] = $request->boolean($flag);
        }

        $stored = [];
        $retired = [];

        try {
            if ($request->hasFile('main_image')) {
                $stored['main_image'] = $this->media->store($request->file('main_image'), 'main_image');
            }
            $gallery = [];
            foreach ($request->file('photos', []) as $index => $photo) {
                $gallery[] = $stored[] = $this->media->store($photo, 'photos.'.$index);
            }

            $product = DB::transaction(function () use ($request, $product, $attributes, $stored, $gallery, &$retired) {
                $record = $product->exists ? Product::lockForUpdate()->findOrFail($product->id) : $product;
                $record->fill($attributes);
                if (! $record->exists) {
                    $record->slug = (Str::slug($record->name) ?: 'dress').'-'.Str::lower((string) Str::ulid());
                }
                if (isset($stored['main_image'])) {
                    $retired[] = $record->main_image;
                    $record->main_image = $stored['main_image'];
                    $record->main_image_uploaded_at = now();
                }
                $record->save();

                $remove = $record->photos()->whereIn('id', ($request->validated('remove_photos') ?? []));
                if ($record->photos()->count() - (clone $remove)->count() + count($gallery) > 12) {
                    throw ValidationException::withMessages(['photos' => 'A dress can have up to 12 additional photos.']);
                }

                $retired = array_merge($retired, (clone $remove)->pluck('path')->all());
                $remove->delete();
                $record->photos()->createMany(array_map(fn ($path) => ['path' => $path], $gallery));

                return $record;
            });
        } catch (Throwable $error) {
            $this->media->discard($stored);
            throw $error;
        }

        $this->media->discard($retired);

        return $product;
    }
}
