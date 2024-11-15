<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    /** @use HasFactory<\Database\Factories\BillFactory> */
    use HasFactory;

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    protected $fillable = [
        'provider_id',
        'bill_number',
        'bill_date',
        'file_url',
        'image_urls',
        'file_type',
    ];
}
