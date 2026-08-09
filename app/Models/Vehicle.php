<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    protected $fillable = [
    'person_id',
    'plate',
    'brand',
    'model',
    'year',
    'color',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
