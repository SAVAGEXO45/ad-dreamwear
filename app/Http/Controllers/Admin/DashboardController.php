<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPhoto;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $mainPhotos = Product::whereNotNull('main_image_uploaded_at')->latest('main_image_uploaded_at')
            ->orderByDesc('id')->limit(6)->get()->map(fn (Product $product) => [
                'product' => $product,
                'url' => $product->imageUrl(),
                'kind' => 'Main photograph',
                'date' => $product->main_image_uploaded_at,
            ]);
        $galleryPhotos = ProductPhoto::with('product')->latest()->orderByDesc('id')->limit(6)->get()
            ->map(fn (ProductPhoto $photo) => [
                'product' => $photo->product,
                'url' => $photo->imageUrl(),
                'kind' => 'Gallery photograph',
                'date' => $photo->created_at,
            ]);

        $selected = $request->integer('collection');
        $categories = Category::with('coverProduct')->withCount('products')->orderBy('id')->get();
        $groups = $categories->map(function (Category $category, int $index) use ($request, $selected) {
            $pageName = $index === 0 ? 'page' : 'collection_'.$category->id.'_page';

            return [
                'category' => $category,
                'open' => $selected === $category->id || $request->integer($pageName, 1) > 1,
                'products' => $category->products()->inDisplayOrder()
                    ->simplePaginate(20, pageName: $pageName)
                    ->withQueryString()
                    ->appends(['collection' => $category->id])
                    ->fragment('collection-'.$category->id),
            ];
        });

        return view('admin.dashboard', [
            'groups' => $groups,
            'counts' => [
                'Total products' => Product::count(),
                'Published' => Product::visible()->count(),
                'Unpublished' => Product::where('published', false)->count(),
                'Categories' => $categories->count(),
                'New Arrivals' => Product::where('new_arrival', true)->count(),
            ],
            'recent' => $galleryPhotos->concat($mainPhotos)->sortByDesc('date')->take(6),
        ]);
    }
}
