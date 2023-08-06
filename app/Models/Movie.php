<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = ["title", "synopsis"];

    public function rules() {
        return [
            "title" => "required|min:3|max:255",
            "synopsis" => "required|min:3",
            "image" => "required|file|mimes:jpeg,jpg,png",
            "genres" => "required|array"
        ];
    }

    public function feedback() {
        return [
            "required" => "The :attribute is required",
            "image" => "Invalid image file type"
        ];
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }
}
