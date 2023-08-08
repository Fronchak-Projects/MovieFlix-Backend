<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\EntityNotFoundException;
use App\Exceptions\ForbiddenException;
use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    private Review $review;
    private Movie $movie;
    private User $user;

    public function __construct(Review $review, Movie $movie, User $user)
    {
        $this->review = $review;
        $this->movie = $movie;
        $this->user = $user;
        $this->middleware('jwt.auth')->only(['store', 'update']);
        $this->middleware('role:member|worker|admin')->only(['store']);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $movieId)
    {
        $request->validate($this->review->rules(), $this->review->feedback());
        $movie = $this->movie->with('reviews')->find($movieId);
        if($movie === null) {
            throw new EntityNotFoundException('Movie not found');
        }
        $user = auth()->user();
        $userReview = $movie->reviews->first(function(Review $review) use($user) {
            return $review->user_id === $user->id;
        });
        if($userReview !== null) {
            throw new BadRequestException('You can only set one review per movie');
        }
        $review = new Review();
        $review->fill($request->all());
        $review->user_id = $user->id;
        $movie->reviews()->save($review);
        $dto = [
            'id' => $review->id,
            'comment' => $review->comment,
            'rating' => $review->rating
        ];
        return response($dto, 201);
    }

    public function update(Request $request, $id) 
    {
        $review = $this->review->with('user')->find($id);
        if($review === null) {
            throw new EntityNotFoundException('Review not found');
        }
        $request->validate($this->review->rules(), $this->review->feedback());  
        $user = auth()->user();
        if($review->user->id !== $user->id) {
            throw new ForbiddenException('You can only change your own reviews');
        }
        $review->fill($request->all());
        $review->update();
        $dto = [
            'id' => $review->id,
            'comment' => $review->comment,
            'rating' => $review->rating
        ];
        return response($dto);
    }

    public function movieReviews($movieId) 
    {
        $reviews = $this->review->with('user')->where('movie_id', '=', $movieId)->get();
        $dtos = $reviews->map(function(Review $review) {
            $user = $review->user;
            return [
                'id' => $review->id,
                'comment' => $review->comment,
                'rating' => $review->rating,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image' => $user->image
                ]
            ];
        });
        return response($dtos);
    }

    public function userReviews($userId)
    {
        $reviews = $this->review->with('movie')->where('user_id', '=', $userId)->get();
        $dtos = $reviews->map(function(Review $review) {
            $movie = $review->movie;
            return [
                'id' => $review->id,
                'comment' => $review->comment,
                'rating' => $review->rating,
                'movie' => [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'image' => $movie->image
                ]
            ];
        });
        return response($dtos);
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        //
    }
}
