<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revision extends Model
{
    protected $fillable = [
    'vehicle_id',
    'revision_date',
    'mileage',
    'description',
    'cost',
    'next_revision_date',
    ];

    protected function casts(): array
    {
        return [
            'revision_date' => 'date',
            'next_revision_date' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
