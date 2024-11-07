<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    /** @use HasFactory<\Database\Factories\ProviderFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'red',
        'green',
        'blue',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function getColorAttribute()
    {
        return '#' . dechex($this->red) . dechex($this->green) . dechex($this->blue);
    }

    public function __toString()
    {
        return $this->name;
    }

    # function to return all the providers name with this format ["provider1", "provider2", "provider3"]
    



}
