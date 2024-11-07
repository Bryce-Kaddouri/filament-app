<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrendProviderPriceByWeek extends Model
{
    // Specify the table name since it doesn't follow the default Laravel naming convention
    protected $table = 'trend_provider_price_by_week';

    // Disable timestamps, as views generally don't have `created_at` or `updated_at` fields
    public $timestamps = false;

    // Add any other properties or methods you need
}
