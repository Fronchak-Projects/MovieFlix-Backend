<?php

namespace App\Mappers;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserMapper 
{
    public static function mapToDTO(User $user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'image' => $user->image,
            'roles' => $user->roles->map(function($role) {
                return $role->name;
            })
        ];
    }

    public static function mapToDTOs(Collection $users) {
        return $users->map(function($user) {
            return UserMapper::mapToDTO($user);
        });
    }
}

?>