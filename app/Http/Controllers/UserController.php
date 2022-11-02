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
    public function add(Request $request)
    {
        $response =
            $this->validate(
                $request,
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required'
                ]
            );

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        if ($user->save()) {
            $response = response()->json(
                [
                    'response' => [
                        'created' => true,
                        'userId' => $user->id
                    ]
                ],
                201
            );
        }
        return $response;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);
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
