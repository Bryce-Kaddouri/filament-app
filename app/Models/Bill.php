<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ZeeshanTariq\FilamentAttachmate\Core\InteractsWithAttachments;

class Bill extends Model
{
    /** @use HasFactory<\Database\Factories\BillFactory> */
    use HasFactory;
    use InteractsWithAttachments;

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    protected $fillable = [
        'provider_id',
        'bill_number',
        'bill_date',
        'file_url',
    ];
}
