<?php

namespace App\Models;

use App\Services\CatalogueMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'sizes', 'price', 'main_image',
        'available', 'featured', 'new_arrival', 'published', 'sort_order', 'main_image_uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'main_image_uploaded_at' => 'datetime', 'price' => 'decimal:2', 'sizes' => 'array', 'available' => 'boolean', 'featured' => 'boolean',
            'new_arrival' => 'boolean', 'published' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProductPhoto::class)->orderBy('id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeInDisplayOrder(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function displayPrice(): string
    {
        [$whole, $fraction] = explode('.', $this->price);

        return '₦'.number_format((int) $whole).($fraction === '00' ? '' : '.'.$fraction);
    }

    public function imageUrl(): string
    {
        return CatalogueMedia::bundled($this->main_image)
            ? asset($this->main_image)
            : route('media.product', [$this->id, basename($this->main_image)]);
    }
}
