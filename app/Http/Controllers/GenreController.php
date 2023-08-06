<?php

namespace App\Http\Controllers;

use App\Mappers\GenreMapper;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    private Genre $genre;

    public function __construct(Genre $genre) {
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
    }

    /**
     * Display the specified resource.
     */
    public function show(Genre $genre)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Genre $genre)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Genre $genre)
    {
        //
    }
}
