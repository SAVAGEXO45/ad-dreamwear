<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function coverProduct(): HasOne
    {
        return $this->hasOne(Product::class)->ofMany(
            ['sort_order' => 'min', 'id' => 'min'],
            fn (Builder $query) => $query->visible()
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
