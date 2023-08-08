<?php

namespace App\Http\Controllers;

use App\Exceptions\EntityNotFoundException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\UnprocessableException;
use App\Mappers\UserMapper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use stdClass;

class UserController extends Controller
{
    private User $user;

    public function __construct(User $user)
    {
        $this->middleware('jwt.auth')->only(['index', 'show', 'update', 'destroy', 'me', 'updateRoles']);
        $this->middleware('role:worker|admin')->only(['index', 'show', 'updateRoles']);
        $this->middleware('role:admin')->only('destroy');
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

    public function me() 
    {
        $user = auth()->user();
        $dto = UserMapper::mapToDTO($user);
        return response($dto);
    }

    public function updateRoles(Request $request, $id) {
        $authenticatedUser = auth()->user();
        $user = $this->getUserById($id);
        $rules = [
            'roles' => 'required|array'
        ];
        $feedback = [
            'required' => 'The :attribute is required'
        ];
        $request->validate($rules, $feedback);
        $role = new Role();
        $roles = $role->whereIn('id', $request->get('roles'))->get();

        if($roles->count() === 0) {
            $errors = new stdClass;
            $errors->genres = ['No roles found'];
            throw new UnprocessableException($errors);
        }

        if($authenticatedUser->id === $user->id) {
            throw new ForbiddenException('You cannot change your own roles');
        }

        $userAuthenticatedIsAdmin = $authenticatedUser->hasRole('admin');
        $userIsAdmin = $user->hasRole('admin');

        if($userIsAdmin && !$userAuthenticatedIsAdmin) {
            throw new ForbiddenException('You dont have permisstion to change the roles of a admin user');
        }

        $user->syncRoles($roles);
        $user = $this->getUserById($id);
        $dto = UserMapper::mapToDTO($user);
        return response($dto);
    }
}
