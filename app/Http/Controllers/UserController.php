<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

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
    public function createNewUser(Request $request)
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
        $user->password = $request->password;
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
}
