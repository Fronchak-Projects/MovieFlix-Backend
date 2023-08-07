<?php

namespace App\Http\Controllers;

use App\Exceptions\EntityNotFoundException;
use App\Mappers\UserMapper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function index() 
    {
        $users = $this->user->all();
        $dtos = UserMapper::mapToDTOs($users);
        return response($dtos);
    }

    public function show($id)
    {
        $user = $this->getUserById($id);
        $dto = UserMapper::mapToDTO($user);
        return response($dto);
    }

    protected function getUserById($id): User
    {
        $user = $this->user->find($id);
        if($user === null) {
            throw new EntityNotFoundException('User not found');
        }
        return $user;
    }

    public function update(Request $request) {
        $user = auth()->user();
        $rules = $user->rules();
        $parameters = $request->all();
        $hasImage = array_key_exists('image', $parameters);

        unset($rules['email']);
        unset($rules['password']);
        unset($rules['confirm_password']);
        if(!$hasImage) {
            unset($rules['image']);
        }

        $request->validate($rules, $parameters);

        $oldImage = $user->image;
        $user->name = $request->get('name');
        if($hasImage) {
            $image = $request->file('image');
            $imageUrn = $image->store('imgs/users', 'public');
            $user->image = $imageUrn;
        }

        $user->update();

        if(!is_null($oldImage) && $hasImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return response('');
    }

    public function destroy($id) 
    {
        $user = $this->getUserById($id);
        $image = $user->image;

        $user->roles()->detach();
        $user->delete();

        if(!is_null($image)) 
        {
            Storage::disk('public')->delete($image);
        }

        return response('', 204);
    }
}
