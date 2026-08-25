<?php

namespace App\Models;

use App\Support\HttpImage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'image'];

    protected function imageSrc(): Attribute
    {
        return Attribute::get(fn () => HttpImage::src($this->image));
    }

    public function products() : HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }
}
