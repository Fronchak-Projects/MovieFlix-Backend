<?php

namespace App\Http\Controllers;

use App\Exceptions\EntityNotFoundException;
use App\Mappers\UserMapper;
use App\Models\User;
use Illuminate\Http\Request;

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
}
