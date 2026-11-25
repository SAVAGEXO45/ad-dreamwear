<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplied_collection_has_eight_public_dresses_at_the_initial_price(): void
    {
        $this->seed();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Elegance Begins')
            ->assertSee('Browse Lounge Dress')
            ->assertSee('New Arrivals')
            ->assertSee('tel:+2348122965207')
            ->assertSee('tel:+2347046205335')
            ->assertSee('mailto:titilolaraji20@gmail.com')
            ->assertSee('https://wa.me/2348122965207');
        $this->assertDatabaseCount('products', 8);
        $catalogue = $this->get(route('catalogue'))->assertOk()->assertSee('<h1>Lounge Dress</h1>', false)->assertSee('₦6,000');

        foreach (Product::all() as $product) {
            $this->assertFileExists(public_path($product->main_image));
            $catalogue->assertSee($product->name);
            $this->assertSame('6000.00', $product->price);
            $this->get(route('dresses.show', $product->slug))
                ->assertOk()->assertDontSee($product->description)->assertSee('₦6,000');
        }

        $this->get(route('catalogue', ['collection' => 'lounge-dresses']))->assertOk()->assertSee('Lounge Dress');
        $this->get(route('catalogue', ['view' => 'new']))->assertOk()
            ->assertSee('Malibu Blue Lounge Dress')->assertDontSee('Honey Graphic Lounge Dress');
    }

    public function test_collection_icons_follow_the_first_published_product_in_display_order(): void
    {
        $this->seed();
        $first = Product::where('slug', 'rose-lace')->firstOrFail();
        $next = Product::where('slug', 'cloud-cami')->firstOrFail();
        $first->update(['published' => false]);
        $cover = 'class="collection-icon" src="'.e($next->imageUrl()).'"';
        $draft = 'class="collection-icon" src="'.e($first->imageUrl()).'"';

        $this->get(route('home'))->assertSee($cover, false)->assertDontSee($draft, false);
        $this->actingAs($this->admin());
        $this->get(route('admin.categories.index'))->assertOk()->assertSee($cover, false);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee($cover, false);

        $first->update(['published' => true, 'sort_order' => $next->sort_order]);
        $this->get(route('home'))->assertSee($draft, false)->assertDontSee($cover, false);
    }

    public function test_public_collections_are_browsed_separately(): void
    {
        $this->seed();
        $dress = Product::where('slug', 'rose-lace')->firstOrFail();
        $shortSets = Category::create(['name' => 'Short Set', 'slug' => 'short-set']);
        $set = $dress->replicate()->fill([
            'category_id' => $shortSets->id,
            'name' => 'Linen Short Set',
            'slug' => 'linen-short-set',
            'new_arrival' => true,
        ]);
        $set->save();
        $lounge = ['collection' => $dress->category->slug];
        $short = ['collection' => $shortSets->slug];

        $this->get(route('home'))->assertOk()->assertSee(route('catalogue', $lounge))->assertSee(route('catalogue', $short));
        $this->get(route('catalogue'))->assertOk()->assertSee('Browse collections')
            ->assertSee('Lounge Dress')->assertSee('Short Set')
            ->assertDontSee($dress->name)->assertDontSee($set->name);
        $this->get(route('catalogue', $lounge))->assertOk()->assertSee($dress->name)->assertDontSee($set->name);
        $this->get(route('catalogue', $short))->assertOk()
            ->assertSee('<h1>Short Set</h1>', false)->assertSee($set->name)->assertDontSee($dress->name)
            ->assertSee(route('catalogue', $short + ['view' => 'new']));
        $this->get(route('catalogue', $short + ['view' => 'new']))->assertOk()
            ->assertSee($set->name)->assertDontSee('Malibu Blue Lounge Dress');
    }

    public function test_admin_routes_reject_guests_and_non_admins_and_sign_in_and_out_work(): void
    {
        $password = 'Test-only Long Password!';
        $admin = $this->admin($password);
        $visitor = User::factory()->create();

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->post(route('admin.products.store'))->assertRedirect(route('login'));
        $this->actingAs($visitor)->get(route('admin.website.edit'))->assertForbidden();
        $this->actingAs($visitor)->delete(route('admin.products.destroy', 999))->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        $this->post(route('login.store'), ['email' => $visitor->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'incorrect'])
            ->assertSessionHasErrors('email');
        $this->post(route('login.store'), ['email' => $admin->email, 'password' => $password])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Recent uploads');
        $this->post(route('admin.logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_manages_all_dresses_from_the_dashboard(): void
    {
        $this->seed();
        $dress = Product::where('slug', 'rose-lace')->firstOrFail();
        $dress->update(['published' => false, 'sizes' => ['M', 'L']]);
        $this->actingAs($this->admin());

        $page = $this->get(route('admin.dashboard'))->assertOk()
            ->assertSee('<h1>Catalogue</h1>', false)
            ->assertSee('Sizes: M, L')
            ->assertSee('Draft')
            ->assertSee(route('admin.products.edit', $dress));

        foreach (Product::all() as $product) {
            $page->assertSee($product->name);
        }
        $this->get(route('admin.products.index'))->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.products.index', ['page' => 2]))
            ->assertRedirect(route('admin.dashboard', ['page' => 2]));
    }

    public function test_admin_dashboard_lists_collections_in_separate_sections(): void
    {
        $this->seed();
        $dress = Product::where('slug', 'rose-lace')->firstOrFail();
        $shortSets = Category::create(['name' => 'Short Set', 'slug' => 'short-set']);
        $set = $dress->replicate()->fill([
            'category_id' => $shortSets->id,
            'name' => 'Test Linen Set',
            'slug' => 'test-linen-set',
        ]);
        $set->save();
        $this->actingAs($this->admin());

        $page = $this->get(route('admin.dashboard'))->assertOk()->getContent();
        $dressGroup = Str::before(Str::after($page, 'data-collection-id="'.$dress->category_id.'"'), '</details>');
        $shortSetGroup = Str::before(Str::after($page, 'data-collection-id="'.$shortSets->id.'"'), '</details>');
        $this->assertStringContainsString('Lounge Dress</strong>', $dressGroup);
        $this->assertStringContainsString($dress->name, $dressGroup);
        $this->assertStringNotContainsString($set->name, $dressGroup);
        $this->assertStringContainsString('Short Set</strong>', $shortSetGroup);
        $this->assertStringContainsString($set->name, $shortSetGroup);
        $this->assertStringNotContainsString($dress->name, $shortSetGroup);

        $link = route('admin.products.create', ['category' => $shortSets->id]);
        $this->assertStringContainsString(e($link), $shortSetGroup);
        $this->get($link)->assertOk()->assertSee('<option value="'.$shortSets->id.'" selected>', false);

        $openPage = $this->get(route('admin.dashboard', ['collection' => $shortSets->id]))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~<details[^>]*data-collection-id="'.$shortSets->id.'"[^>]*\sopen\s*>~', $openPage);
    }

    public function test_repeated_admin_sign_in_failures_are_throttled(): void
    {
        $admin = $this->admin('Test-only Long Password!');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'wrong'])
                ->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'Test-only Long Password!'])
            ->assertSessionHasErrors(['email' => 'Too many attempts. Try again in a minute.']);
        $this->assertGuest();
    }

    public function test_console_provisions_an_admin_with_a_hashed_password(): void
    {
        $password = 'Test-only Console Password!';
        $this->artisan('catalogue:admin', ['email' => 'console@example.test'])
            ->expectsQuestion('Password (at least 14 characters)', $password)
            ->expectsQuestion('Repeat password', $password)
            ->assertSuccessful();

        $user = User::where('email', 'console@example.test')->firstOrFail();
        $this->assertTrue((bool) $user->is_admin);
        $this->assertNotSame($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->artisan('catalogue:admin', ['email' => $user->email])->assertFailed();
    }

    public function test_owner_creates_edits_reorders_and_deletes_dresses_with_real_uploads(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());
        $this->seed();
        $category = Category::firstOrFail();
        $fields = [
            'category_id' => $category->id,
            'name' => 'Sample Stripe Dress',
            'price' => '7450.25',
            'description' => 'Short sleeves and a striped pattern.',
            'sort_order' => 50,
            'available' => 1,
            'published' => 0,
        ];

        $this->get(route('admin.products.create'))->assertOk();
        $this->post(route('admin.products.store'), $fields + [
            'main_image' => UploadedFile::fake()->image('front.jpg'),
            'photos' => [UploadedFile::fake()->image('side.png'), UploadedFile::fake()->image('back.jpg')],
        ])->assertRedirect();

        $product = Product::where('name', $fields['name'])->firstOrFail();
        $original = $product->main_image;
        $photos = $product->photos()->get();
        $this->assertCount(2, $photos);
        Storage::disk('local')->assertExists([$original, ...$photos->pluck('path')]);
        $this->get(route('admin.products.edit', $product))->assertOk()->assertSee('Gallery photo 1');
        $this->get($product->imageUrl())->assertOk()->assertHeader('Content-Type', 'image/webp');

        $this->post(route('admin.logout'));
        $this->get(route('dresses.show', $product->slug))->assertNotFound();
        $this->get($product->imageUrl())->assertNotFound();
        $this->get($photos[0]->imageUrl())->assertNotFound();
        $this->get(route('catalogue'))->assertDontSee($product->name);

        $this->actingAs($this->admin());
        $updates = array_replace($fields, [
            'name' => 'Weekend Stripe Dress', 'price' => '8100.50',
            'description' => 'A revised description.', 'sort_order' => 1,
            'published' => 1, 'available' => 0, 'new_arrival' => 1, 'featured' => 1,
        ]);

        $this->from(route('admin.products.edit', $product))->put(route('admin.products.update', $product), $updates + [
            'main_image' => UploadedFile::fake()->image('updated.jpg'),
            'remove_photos' => [$photos[0]->id],
        ])->assertRedirect(route('admin.products.edit', $product));

        $product->refresh();
        $this->assertSame('8100.50', $product->price);
        $this->assertSame(1, $product->photos()->count());
        Storage::disk('local')->assertMissing([$original, $photos[0]->path]);
        $this->get(route('dresses.show', $product->slug))->assertOk()
            ->assertSee('Currently unavailable')->assertDontSee('A revised description.')->assertSee('₦8,100.50')->assertSee('Show photograph 2');
        $this->get(route('catalogue', ['view' => 'new']))->assertSee($product->name);
        $this->get(route('catalogue'))->assertSeeInOrder([$product->name, 'Rose Lace Lounge Dress']);
        $this->get(route('admin.dashboard'))->assertSee('Gallery photograph')->assertSee($product->name);

        $this->put(route('admin.products.update', $product), array_replace($updates, ['published' => 0]))->assertRedirect();
        $this->get(route('dresses.show', $product->slug))->assertNotFound();
        $this->delete(route('admin.products.destroy', $product))->assertRedirect(route('admin.dashboard'));
        $this->assertModelMissing($product);
        Storage::disk('local')->assertMissing([$product->main_image, $photos[1]->path]);
    }

    public function test_single_upload_is_supported_and_invalid_files_and_prices_are_rejected(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());
        $category = Category::create(['name' => 'Test Collection', 'slug' => 'test']);
        $fields = [
            'category_id' => $category->id, 'name' => 'Single photo dress',
            'sort_order' => 10,
        ];

        $this->post(route('admin.products.store'), $fields + [
            'price' => '-1.123',
            'main_image' => UploadedFile::fake()->createWithContent('fake.jpg', '<?php echo "fake";'),
        ])->assertSessionHasErrors(['price', 'main_image']);
        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());

        $this->post(route('admin.products.store'), $fields + [
            'price' => '6000', 'main_image' => UploadedFile::fake()->image('front.jpg'),
        ])->assertSessionHasNoErrors();
        $product = Product::firstOrFail();
        $this->assertCount(0, $product->photos);
        $this->assertSame('', $product->description);
        $this->get(route('admin.products.edit', $product))->assertSee('Description (optional)');
        $this->put(route('admin.products.update', $product), $fields + [
            'price' => '6000', 'description' => 'Private dress description.',
        ])->assertSessionHasNoErrors();
        $this->get(route('admin.products.edit', $product))->assertSee('Private dress description.');
        $this->put(route('admin.products.update', $product), $fields + [
            'price' => '6000', 'description' => '',
        ])->assertSessionHasNoErrors();
        $this->assertSame('', $product->fresh()->description);
        Storage::disk('local')->assertExists($product->main_image);
    }

    public function test_owner_can_manage_collections_and_cannot_delete_one_in_use(): void
    {
        $this->actingAs($this->admin());
        $this->seed();
        $initial = Category::firstOrFail();
        $this->get(route('admin.categories.index'))->assertOk();
        $this->get(route('admin.categories.create'))->assertOk();
        $this->post(route('admin.categories.store'), [
            'name' => 'Weekend', 'description' => 'Weekend styles.',
        ])->assertRedirect(route('admin.categories.index'));
        $category = Category::where('name', 'Weekend')->firstOrFail();

        $this->get(route('admin.categories.edit', $category))->assertOk();
        $this->put(route('admin.categories.update', $category), [
            'name' => 'Weekend Edit', 'description' => 'A revised collection.',
        ])->assertRedirect(route('admin.categories.index'));
        $this->assertSame('Weekend Edit', $category->fresh()->name);
        $this->delete(route('admin.categories.destroy', $initial))->assertSessionHasErrors('category');
        $this->assertModelExists($initial);
        $this->delete(route('admin.categories.destroy', $category))->assertRedirect(route('admin.categories.index'));
        $this->assertModelMissing($category);
    }

    public function test_website_edits_appear_publicly_and_banners_can_be_replaced_and_removed(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());
        $this->get(route('admin.website.edit'))->assertOk();
        $content = Arr::except(config('catalogue.defaults'), ['banner_desktop', 'banner_mobile']);
        $edited = array_replace($content, [
            'hero_heading' => 'The New Lounge Edit',
            'hero_description' => 'Browse our latest designs.',
            'about_text' => 'This about text was updated.',
            'phone_primary' => '0801 234 5678',
            'phone_secondary' => null,
            'whatsapp' => '0802 345 6789',
            'email' => 'hello@example.test',
            'instagram' => 'https://instagram.com/example',
            'announcement' => 'A new style just landed',
            'footer_text' => 'A new footer message.',
        ]);

        $this->from(route('admin.website.edit'))->put(route('admin.website.update'), $edited + [
            'banner_desktop' => UploadedFile::fake()->image('desktop.jpg'),
            'banner_mobile' => UploadedFile::fake()->image('mobile.jpg'),
        ])->assertRedirect(route('admin.website.edit'))->assertSessionHasNoErrors();
        $site = SiteSetting::current();
        Storage::disk('local')->assertExists([$site['banner_desktop'], $site['banner_mobile']]);
        $this->get(SiteSetting::bannerUrl($site['banner_desktop']))->assertOk();
        $this->get(route('home'))->assertOk()
            ->assertSee('The New Lounge Edit')->assertSee('Browse our latest designs.')->assertSee('This about text was updated.')
            ->assertSee('A new style just landed')->assertSee('A new footer message.')
            ->assertSee('tel:+2348012345678')->assertDontSee('0704 620 5335')
            ->assertSee('mailto:hello@example.test')->assertSee('https://wa.me/2348023456789')
            ->assertSee('https://instagram.com/example')->assertSee('@example')
            ->assertSee(SiteSetting::bannerUrl($site['banner_mobile']));

        $this->put(route('admin.website.update'), array_replace($edited, [
            'announcement' => null, 'remove_banner_desktop' => 1, 'remove_banner_mobile' => 1,
        ]))->assertSessionHasNoErrors();

        Storage::disk('local')->assertMissing([$site['banner_desktop'], $site['banner_mobile']]);
        $this->get(SiteSetting::bannerUrl($site['banner_desktop']))->assertNotFound();
        $this->get(route('home'))->assertOk()->assertDontSee('A new style just landed');
    }

    private function admin(string $password = 'Test-only Long Password!'): User
    {
        return User::factory()->create(['password' => $password, 'is_admin' => true]);
    }
}
