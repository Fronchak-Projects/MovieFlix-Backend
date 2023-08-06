<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\EntityNotFoundException;
use App\Mappers\GenreMapper;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GenreController extends Controller
{
    private Genre $genre;

    public function __construct(Genre $genre) {
        $this->middleware('jwt.auth')->only(['store', 'update', 'destroy']);
        $this->genre = $genre;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $genres = $this->genre->all();
        $dtos = GenreMapper::mapToDTOs($genres);
        return response($dtos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = $this->genre->rules();
        $parameters = $request->all();
        $hasImage = array_key_exists('image', $parameters);
        if(!$hasImage) {
            unset($rules['image']);
        }
        $request->validate($rules, $this->genre->feedback());

        $genre = new Genre();
        $genre->fill($request->all());

        if($hasImage) {
            $image = $request->file('image');
            $imageUrn = $image->store('imgs/genres', 'public');
            $genre->image = $imageUrn;
        }

        $genre->save();
        $dto = GenreMapper::mapToDTO($genre);
        return response($dto, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $genre = $this->getGenreById($id);
        $dto = GenreMapper::mapToDTO($genre);
        return response($dto);
    }

    private function getGenreById($id): Genre {
        $genre = $this->genre->find($id);
        if($genre === null) {
            throw new EntityNotFoundException('Genre not found');
        }
        return $genre;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $genre = $this->getGenreById($id);
        $rules = $genre->rules();
        $parameters = $request->all();

        $hasImage = array_key_exists('image', $parameters);
        if(!$hasImage) {
            unset($rules['image']);
        }
        $request->validate($rules, $genre->feedback());

        $oldImage = $genre->image;
        $genre->fill($request->all());

        if($hasImage) {
            $image = $request->file('image');
            $imageUrn = $image->store('imgs/genres', 'public');
            $genre->image = $imageUrn;
        }
        
        $genre->update();

        if(!is_null($oldImage) && $hasImage) {
            Storage::disk('public')->delete($oldImage);
        }

        $dto = GenreMapper::mapToDTO($genre);
        return response($dto);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $genre = $this->getGenreById($id);
        $movies = $genre->movies;
        if($movies->count() > 0) {
            throw new BadRequestException('This genre already has movie(s) associate to it');
        } 

        $oldImage = $genre->image;
        if(!is_null($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }

        $genre->delete();
        return response('', 204);
    }
}
