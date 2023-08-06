<?php

namespace App\Mappers;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Collection;

class MovieMapper 
{
    public static function mapToDTO(Movie $movie) 
    {
        $genresDTO = GenreMapper::mapToDTOs($movie->genres);
        return [
            'id' => $movie->id,
            'title' => $movie->title,
            'synopsis' => $movie->synopsis,
            'image' => $movie->image,
            'genres' => $genresDTO
        ];
    }

    public static function mapToDTOs(Collection $movies) 
    {
        return $movies->map(function($movie) {
            return [
                'id' => $movie->id,
                'title' => $movie->title,
                'image' => $movie->image,
                'genres' => $movie->genres->map(function($genre) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name
                    ];
                })
            ];
        });
    }
}

?>