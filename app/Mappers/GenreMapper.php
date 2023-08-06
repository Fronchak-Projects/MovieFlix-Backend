<?php

namespace App\Mappers;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Collection;

class GenreMapper 
{
    public static function mapToDTO(Genre $genre) 
    {
        return [
            'id' => $genre->id,
            'name' => $genre->name,
            'imageURL' => $genre->imageURL
        ];
    }

    public static function mapToDTOs(Collection $genres) {
        return $genres->map(function($genre) {
            return GenreMapper::mapToDTO($genre);
        });
    }
}
?>
