<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    public $timestamps = false;

    protected $fillable = [
        'rating',
        'comment',
        'movie',
        'user',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
            'created_at' => 'datetime',
        ];
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'movie');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user');
    }
}
