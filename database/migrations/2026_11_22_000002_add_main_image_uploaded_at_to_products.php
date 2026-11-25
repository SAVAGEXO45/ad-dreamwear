<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('main_image_uploaded_at')->nullable()->index();
        });

        DB::table('products')->update(['main_image_uploaded_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['main_image_uploaded_at']);
            $table->dropColumn('main_image_uploaded_at');
        });
    }
};
