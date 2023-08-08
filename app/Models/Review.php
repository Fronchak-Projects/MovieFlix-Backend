<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['comment', 'rating'];

    public function rules() {
        return [
            'rating' => 'required|min:0.0|max:5.0|decimal:0,1'
        ];
    }

    public function feedback() {
        return [
            'rating' => 'The rating is required and must be between 0 and 5'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}
