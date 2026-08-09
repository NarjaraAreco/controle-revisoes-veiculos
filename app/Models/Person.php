<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $fillable = [
    'name',
    'cpf',
    'birth_date',
    'gender',
    'phone',
    'email',
    'cep',
    'street',
    'number',
    'complement',
    'neighborhood',
    'city',
    'state',
];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
