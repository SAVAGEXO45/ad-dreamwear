<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DressSizesTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_change_and_clear_optional_dress_sizes(): void
    {
        $this->seed();
        $product = Product::where('slug', 'rose-lace')->firstOrFail();
        $productUrl = route('dresses.show', $product->slug);
        $update = route('admin.products.update', $product);
        $fields = [
            'name' => $product->name,
            'category_id' => $product->category_id,
            'price' => $product->price,
            'description' => $product->description,
            'sort_order' => $product->sort_order,
            'published' => true,
            'available' => true,
        ];

        $this->assertNull($product->sizes);
        $this->get($productUrl)->assertOk()->assertSee('Enquire about available sizes');
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get(route('admin.products.edit', $product))->assertOk()->assertSee('Available sizes');

        $this->put($update, $fields + ['sizes' => 'M, L, m, , UK 12'])->assertSessionHasNoErrors();
        $this->assertSame(['M', 'L', 'UK 12'], $product->fresh()->sizes);
        $this->get(route('catalogue'))->assertOk()->assertSee('Sizes M, L, UK 12');
        $this->get($productUrl)->assertSee('Sizes: M, L, UK 12');

        $this->put($update, $fields)->assertSessionHasNoErrors();
        $this->assertSame(['M', 'L', 'UK 12'], $product->fresh()->sizes);
        $this->put($update, $fields + ['sizes' => str_repeat('X', 25)])->assertSessionHasErrors('sizes');
        $this->assertSame(['M', 'L', 'UK 12'], $product->fresh()->sizes);

        $this->put($update, $fields + ['sizes' => 'One size'])->assertSessionHasNoErrors();
        $this->assertSame(['One size'], $product->fresh()->sizes);
        $this->put($update, $fields + ['sizes' => ''])->assertSessionHasNoErrors();
        $this->assertNull($product->fresh()->sizes);
        $this->get($productUrl)->assertSee('Enquire about available sizes');
    }

    public function test_renaming_the_launch_collection_preserves_its_link_and_products(): void
    {
        $this->seed();
        $product = Product::where('slug', 'rose-lace')->firstOrFail();
        $category = $product->category;
        $category->update(['name' => 'Lounge Dresses']);

        $rename = require database_path('migrations/2026_11_23_000002_rename_default_lounge_collection.php');
        $rename->up();

        $this->assertSame('Lounge Dress', $category->fresh()->name);
        $this->assertSame('lounge-dresses', $category->fresh()->slug);
        $this->assertSame($category->id, $product->fresh()->category_id);
        $this->get(route('catalogue', ['collection' => 'lounge-dresses']))->assertOk()->assertSee($product->name);
    }
}
