<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductChartFilterModel extends Model
{
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected $fillable = ['year', 'product_id', 'provider_id'];
}
