<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
class Verification extends Model
{
    protected $fillable = ['is_success', 'reason', 'configuration_id'];

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(Configuration::class);
    }

    // default order by created_at desc
    public function newEloquentBuilder($query)
    {
        $query->orderBy('id', 'desc');
        return new Builder($query);
    }
}
