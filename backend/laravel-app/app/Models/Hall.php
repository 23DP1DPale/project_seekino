<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hall extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'seat_amount',
    ];

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class, 'hall');
    }
}
