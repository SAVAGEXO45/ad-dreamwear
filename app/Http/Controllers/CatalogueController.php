<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogueController extends Controller
{
    public function home(): View
    {
        return view('catalogue.home', [
            'site' => SiteSetting::current(),
            'categories' => $this->categories(),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'collection' => ['nullable', 'string', 'max:255'],
            'view' => ['nullable', 'in:new,featured'],
        ]);
        $category = empty($filters['collection'])
            ? null
            : Category::where('slug', $filters['collection'])->whereHas('products', fn ($query) => $query->visible())->firstOrFail();
        $selection = $filters['view'] ?? null;

        $products = Product::visible()->with('category')->inDisplayOrder();
        if ($category) {
            $products->whereBelongsTo($category);
        }
        if ($selection) {
            $products->where($selection === 'new' ? 'new_arrival' : 'featured', true);
        }

        $categories = $this->categories();
        $chooseCollection = ! $category && ! $selection && $categories->count() > 1;
        $collection = $category ?? ($categories->count() === 1 ? $categories->first() : null);

        return view('catalogue.index', [
            'site' => SiteSetting::current(),
            'products' => $chooseCollection ? null : $products->paginate(12)->withQueryString(),
            'categories' => $categories,
            'category' => $category,
            'collection' => $collection,
            'selection' => $selection,
            'chooseCollection' => $chooseCollection,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->published, 404);
        $product->load(['photos', 'category']);

        return view('catalogue.show', [
            'site' => SiteSetting::current(),
            'categories' => $this->categories(),
            'product' => $product,
            'related' => $product->category->products()->visible()->whereKeyNot($product->id)->inDisplayOrder()->limit(4)->get(),
        ]);
    }

    private function categories()
    {
        return Category::query()->whereHas('products', fn ($query) => $query->visible())
            ->with('coverProduct')
            ->withCount(['products' => fn ($query) => $query->visible()])
            ->orderBy('name')->get();
    }
}
