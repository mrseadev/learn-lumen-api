<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    protected function validateAddRequest(Request $request)
    {
        try {
            $this->validate(
                $request,
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required'
                ],
                [
                    'name.required' => 'Name is required',
                    'email.required' => 'Email is required',
                    'email.email' => 'Email is invalid',
                    'email.unique' => 'Email already exists',
                    'password.required' => 'Password is required'
                ]
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'errors' =>  $e->validator->errors(),
                'message' => 'Validation error'
            ], 500);
        }

        return true;
    }

    protected function validateLoginRequest(Request $request)
    {
        try {
            $this->validate(
                $request,
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ],
                [
                    'email.required' => 'Email is required',
                    'email.email' => 'Email is invalid',
                    'password.required' => 'Password is required'
                ]
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'errors' =>  $e->validator->errors(),
                'message' => 'Validation error'
            ], 500);
        }

        return true;
    }

    public function add(Request $request)
    {
        $validate = $this->validateAddRequest($request);
        if ($validate !== true) {
            return $validate;
        }

        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'error' => false,
                'message' => 'User added successfully',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validate = $this->validateLoginRequest($request);
        if ($validate !== true) {
            return $validate;
        }

        $user = User::where('email', $request->input('email'))->first();
        if (Hash::check($request->input('password'), $user->password)) {
            $api_token = base64_encode(str_random(40));
            User::where('email', $request->input('email'))->update(['api_token' => "$api_token"]);
            return response()->json(['error' => false, 'message' => 'Login success', 'api_token' => $api_token]);
        } else {
            return response()->json(['error' => true, 'message' => 'Login fail'], 401);
        }
    }

    public function refreshToken(Request $request)
    {
        $api_token = $request->header('API-Token');
        $user = User::where('api_token', $api_token)->first();
        if ($user) {
            $api_token = base64_encode(str_random(40));
            User::where('api_token', $api_token)->update(['api_token' => "$api_token"]);
            return response()->json(['error' => false, 'message' => 'Token refreshed', 'api_token' => $api_token]);
        } else {
            return response()->json(['error' => true, 'message' => 'Token refresh fail'], 401);
        }
    }
}
