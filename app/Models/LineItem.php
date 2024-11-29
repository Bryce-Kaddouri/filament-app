<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    // get provider
    public function provider(){
        return $this->belongsTo(Provider::class);
    }

    public function bill(){
        return $this->belongsTo(Bill::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    protected $fillable = [
        'bill_id',
        'provider_id',
        'quantity',
        'unit_price',
        'product_id'
    ];
}
