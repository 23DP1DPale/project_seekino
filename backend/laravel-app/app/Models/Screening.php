<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Screening extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'screening_date',
        'screening_time',
        'cost',
        'hall',
        'movie',
    ];

    protected function casts(): array
    {
        return [
            'screening_date' => 'date:Y-m-d',
            'cost' => 'decimal:2',
        ];
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class, 'hall');
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'movie');
    }
}
