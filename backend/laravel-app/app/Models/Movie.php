<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'length',
        'description',
        'director',
        'image',
        'age_restriction',
    ];

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'movies_genres_usage', 'movie', 'genre')
            ->withPivot('primary_genre');
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class, 'movie');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class, 'movie');
    }
}
