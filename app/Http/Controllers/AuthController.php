<?php

namespace App\Http\Controllers;

use App\Exceptions\UnauthorizedException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth')->only(['update', 'changePassword']);
    }

    public function login() {
        $credentials = request(['email', 'password']);
        return $this->authenticate($credentials);
    }

    protected function authenticate($credentials) {
        $token = auth()->attempt($credentials);
        if(!$token) {
            throw new UnauthorizedException('Invalid email or passowrd');
        }
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token) {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer'
        ]);
    }

    public function me() {
        return response()->json(auth()->user());
    }

    public function logout() {
        auth()->logout();
        return response('', 204);
    }

    public function refresh() {
        return $this->responseWithToken(auth()->refresh());
    }

    public function register(Request $request) {
        $user = new User();
        $rules = $user->rules();
        unset($rules['image']);

        $request->validate($rules, $user->feedback());

        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $password = $request->get('password');

        $encrypted = bcrypt($password);
        $user->password = $encrypted;
        $user->save();
        $user->assignRole('user');

        return $this->authenticate([
            'email' => $user->email,
            'password' => $request->get('password')
        ]);
    }

    public function changePassword(Request $request) {
        $user = auth()->user();
        $rules = [
            'old_password' => 'required',
            'new_password' => 'required|min:4',
            'confirm_new_password' => 'required|same:new_password'
        ];
        $feedback = [
            'required' => 'The :attribute is required'
        ];
        $request->validate($rules, $feedback);

        $token = auth()->attempt([
            'email' => $user->email,
            'password' => $request->get('old_password')
        ]);

        if(!$token) {
            throw new UnauthorizedException('Invalid old password');
        }

        $encrypted = bcrypt($request->get('new_password'));
        $user->password = $encrypted;
        $user->update();

        return response('');
    }
}
