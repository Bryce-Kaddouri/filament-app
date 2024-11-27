<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCodeByProvider extends Model
{
    protected $casts = [
        'product_code_by_provider' => 'array',
    ];

    protected $fillable = [
        'product_id',
        'provider_id',
        'code'
    ];
}
