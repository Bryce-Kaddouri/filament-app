<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    /** @use HasFactory<\Database\Factories\ProviderFactory> */
    use HasFactory;

    

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

    /**
     * REPLACE THE FOLLOWING ARRAYS IN YOUR Provider MODEL
     *
     * Replace your existing $fillable and/or $guarded and/or $appends arrays with these - we already merged
     * any existing attributes from your model, and only included the one(s) that need changing.
     */


     protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'red',
        'green',
        'blue',
        'location',
        'zip',
        'state',
        'city',
        'street',
        'full_address',
    ];

    protected $appends = [
        'location',
    ];

    /**
     * ADD THE FOLLOWING METHODS TO YOUR Provider MODEL
     *
     * The 'lat' and 'lng' attributes should exist as fields in your table schema,
     * holding standard decimal latitude and longitude coordinates.
     *
     * The 'location' attribute should NOT exist in your table schema, rather it is a computed attribute,
     * which you will use as the field name for your Filament Google Maps form fields and table columns.
     *
     * You may of course strip all comments, if you don't feel verbose.
     */

    /**
    * Returns the 'lat' and 'lng' attributes as the computed 'location' attribute,
    * as a standard Google Maps style Point array with 'lat' and 'lng' attributes.
    *
    * Used by the Filament Google Maps package.
    *
    * Requires the 'location' attribute be included in this model's $fillable array.
    *
    * @return array
    */

    public function getLocationAttribute(): array
    {
        return [
            "lat" => (float)$this->lat,
            "lng" => (float)$this->lng,
        ];
    }

    /**
    * Takes a Google style Point array of 'lat' and 'lng' values and assigns them to the
    * 'lat' and 'lng' attributes on this model.
    *
    * Used by the Filament Google Maps package.
    *
    * Requires the 'location' attribute be included in this model's $fillable array.
    *
    * @param ?array $location
    * @return void
    */
    public function setLocationAttribute(?array $location): void
    {
        if (is_array($location))
        {
            $this->attributes['lat'] = $location['lat'];
            $this->attributes['lng'] = $location['lng'];
            unset($this->attributes['location']);
        }
    }

    /**
     * Get the lat and lng attribute/field names used on this table
     *
     * Used by the Filament Google Maps package.
     *
     * @return string[]
     */
    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => 'lat',
            'lng' => 'lng',
        ];
    }

   /**
    * Get the name of the computed location attribute
    *
    * Used by the Filament Google Maps package.
    *
    * @return string
    */
    public static function getComputedLocation(): string
    {
        return 'location';
    }

    

    



}
