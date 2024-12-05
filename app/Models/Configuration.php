<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Verification;
class Configuration extends Model
{
    protected $fillable = ['key_path', 'project_id'];

    public static function getConfiguration(): ?self
    {
        return self::first(); // Retrieve the single configuration
    }

    protected function casts(): array
    {
        return [
            'key_path' => 'string',
        ];
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class);
    }
}
