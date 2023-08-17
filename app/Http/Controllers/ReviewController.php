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

    public function __construct(Review $review, Movie $movie)
    {
        $this->review = $review;
        $this->movie = $movie;
        $this->middleware('jwt.auth')->only(['store', 'update', 'destroy', 'myReviews', 'myMovieReview']);
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $review = $this->review->find($id);
        if($review === null) {
            throw new EntityNotFoundException('Review not found');
        }
        $user = auth()->user();
        if($review->user_id !== $user->id) {
            if(!($user->hasRole('admin'))) {
                throw new ForbiddenException('You can only delete your own reviews');
            }
        }
        $review->delete();
        return response('', 204);
    }

    public function myReviews()
    {
        $user = auth()->user();
        $reviews = $this->review->with('movie')->where('user_id', '=', $user->id)->get();
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

    public function myMovieReview($movieId)
    {
        $user = auth()->user();
        $review = $this->review->with('movie')
                ->where('user_id', '=', $user->id)
                ->where('movie_id', '=', $movieId)
                ->get()->first();
        if($review === null) {
            throw new EntityNotFoundException("Review not found");
        }
        $movie = $review->movie;
        $dto = [
            'id' => $review->id,
            'comment' => $review->comment,
            'rating' => $review->rating,
            'movie' => [
                'id' => $movie->id,
                'title' => $movie->title,
                'image' => $movie->image
            ]
        ];
        return response($dto);
    }
}
