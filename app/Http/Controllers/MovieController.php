<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\EntityNotFoundException;
use App\Exceptions\UnprocessableException;
use App\Mappers\MovieMapper;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use stdClass;

class MovieController extends Controller
{
    private Movie $movie;
    private Genre$genre;

    public function __construct(Movie $movie, Genre $genre)
    {
        $this->movie = $movie;
        $this->genre = $genre;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movies = $this->movie->all();
        $dtos = MovieMapper::mapToDTOs($movies);
        return response($dtos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate($this->movie->rules(), $this->movie->feedback());
        
        $movie = new Movie();
        $genres = $this->genre->whereIn('id', $request->get('genres'))->get();
        if($genres->count() === 0) {
            $errors = new stdClass;
            $errors->genres = ['No genres found'];
            throw new UnprocessableException($errors);
        }
        
        $movie->fill($request->all());
        $image = $request->file('image');
        $imageUrn = $image->store('imgs/movies', 'public');
        $movie->image = $imageUrn;

        $genreIds = $genres->map(function($genre) {
            return $genre->id;
        })->all();

        $movie->save();
        $movie->genres()->sync($genreIds);
        $movie = $this->movie->with('genres')->find($movie->id);
        $dto = MovieMapper::mapToDTO($movie);
        return response($dto, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $movie = $this->getMovieById($id);
        $dto = MovieMapper::mapToDTO($movie);
        return response($dto);
    }

    private function getMovieById($id): Movie {
        $movie = $this->movie->with('genres')->find($id);
        if($movie === null) {
            throw new EntityNotFoundException('Movie not found');
        }
        return $movie;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $movie = $this->getMovieById($id);
        $rules = $movie->rules();
        $parameters = $request->all();

        $hasImage = array_key_exists('image', $parameters);
        if(!$hasImage) {
            unset($rules['image']);
        }
        $request->validate($rules, $movie->feedback());
        $genres = $this->genre->whereIn('id', $request->get('genres'))->get();
        if($genres->count() === 0) {
            $errors = new stdClass;
            $errors->genres = ['No genres found'];
            throw new UnprocessableException($errors);
        }

        $oldImage = $movie->image;

        if($hasImage) {
            $image = $request->file('image');
            $imageUrn = $image->store('imgs/movies', 'public');
            $movie->image = $imageUrn;
        }

        $genreIds = $genres->map(function($genre) {
            return $genre->id;
        })->all();

        $movie->fill($parameters);
        $movie->update();
        $movie->genres()->sync($genreIds);

        if($hasImage) {
            Storage::disk('public')->delete($oldImage);
        }

        $movie = $this->movie->with('genres')->find($movie->id);
        $dto = MovieMapper::mapToDTO($movie);
        return response($dto);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $movie = $this->getMovieById($id);
        $image = $movie->image;
        Storage::disk('public')->delete($image);
        $movie->genres()->detach();
        $movie->delete();
        return response('', 204);
    }
}
