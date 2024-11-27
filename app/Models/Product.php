<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    public function providers()
    {
        return $this->belongsToMany(Provider::class)->withTimestamps();
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function products_code_by_provider()
    {
        return $this->hasMany(ProductCodeByProvider::class);
    }

    public function __toString()
    {
        return $this->name;
    }

    #protected fillable fields
    protected $fillable = ['name', 'description', 'image'];

    

}
