<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', [
            'categories' => Category::with('coverProduct')->withCount('products')->orderBy('name')->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $slug = (Str::slug($data['name']) ?: 'collection').'-'.Str::lower((string) Str::ulid());
        Category::create($data + ['slug' => $slug]);

        return redirect()->route('admin.categories.index')->with('status', 'Collection added.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('admin.categories.index')->with('status', 'Collection updated.');
    }

    public function destroy(Category $category)
    {
        $deleted = DB::transaction(function () use ($category) {
            $record = Category::lockForUpdate()->findOrFail($category->id);

            return ! $record->products()->exists() && $record->delete();
        });

        if (! $deleted) {
            return back()->withErrors(['category' => 'Move or delete the dresses in this collection before deleting it.']);
        }

        return redirect()->route('admin.categories.index')->with('status', 'Collection deleted.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $unique = Rule::unique('categories', 'name');
        if ($category) {
            $unique->ignore($category);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:100', $unique],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
