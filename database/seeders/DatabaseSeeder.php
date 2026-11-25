<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(['slug' => 'lounge-dresses'], [
            'name' => 'Lounge Dress',
            'description' => 'Easy short dresses for slow mornings, quiet evenings and everything in between.',
        ]);

        $styles = [
            ['Rose Lace Lounge Dress', 'rose-lace', 'A deep rose slip dress with contrasting pink lace, slim straps and a lace-edged side opening.', true, false],
            ['Cloud Cami Lounge Dress', 'cloud-cami', 'A white cami dress with slim straps, a shallow scoop neckline and a simple short hem.', true, false],
            ['Malibu Blue Lounge Dress', 'malibu-blue', 'A soft blue T-shirt dress with turned-up sleeves, a curved hem and white Malibu lettering.', true, true],
            ['Midnight Stripe Lounge Dress', 'midnight-stripe', 'A navy-and-white striped dress with a round neckline, short sleeves and an easy flared hem.', true, false],
            ['Olive Stripe Lounge Dress', 'olive-stripe', 'A green-and-white striped dress with a button front, olive trim and a contrast chest pocket.', false, true],
            ['Blush Graphic Lounge Dress', 'blush-graphic', 'A pale pink T-shirt dress with roomy sleeves, bold green lettering and a colourful cartoon graphic.', false, true],
            ['Sunshine Graphic Lounge Dress', 'sunshine-graphic', 'A bright yellow short-sleeved dress with a round neckline and an oversized cartoon face print.', false, false],
            ['Honey Graphic Lounge Dress', 'honey-graphic', 'A white graphic dress with a round neckline, roomy sleeves, small bee illustrations and a bold yellow print.', false, false],
        ];

        foreach ($styles as $position => [$name, $slug, $description, $featured, $newArrival]) {
            Product::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'category_id' => $category->id,
                'description' => $description,
                'price' => 6000,
                'main_image_uploaded_at' => now(),
                'main_image' => "collection/{$slug}.jpeg",
                'available' => true,
                'featured' => $featured,
                'new_arrival' => $newArrival,
                'published' => true,
                'sort_order' => ($position + 1) * 10,
            ]);
        }
    }
}
