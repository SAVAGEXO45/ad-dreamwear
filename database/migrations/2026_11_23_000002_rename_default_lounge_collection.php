<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->rename('Lounge Dresses', 'Lounge Dress');
    }

    public function down(): void
    {
        $this->rename('Lounge Dress', 'Lounge Dresses');
    }

    private function rename(string $from, string $to): void
    {
        $category = DB::table('categories')->where('slug', 'lounge-dresses')->first();
        if ($category?->name !== $from || DB::table('categories')->where('name', $to)->exists()) {
            return;
        }

        DB::table('categories')->where('id', $category->id)->update([
            'name' => $to,
            'updated_at' => now(),
        ]);
    }
};
