<?php

namespace App\Http\Controllers;

use App\User;

use Illuminate\Http\Request;

class PostController extends Controller
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

    public function getUserPosts($id)
    {
        $user = User::find($id)->posts()->get();
        return ($user);
    }
}
