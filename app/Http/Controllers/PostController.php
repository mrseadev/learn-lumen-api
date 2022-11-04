<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\User;
use App\Post;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

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
        define('STORE_IMAGE', Config::get('constants.STORE_IMAGE', 'images'));
    }

    public function getUserPosts($id)
    {
        try {
            $user = User::find($id)->posts()->get();
            return response()->json(
                [
                    'error' => false,
                    'message' => 'User posts',
                    'users' => $user
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => true,
                    'message' => $e->getMessage()
                ],
                500
            );
        }
    }

    protected function validateRequest(Request $request)
    {
        try {
            $this->validate($request, [
                'title' => 'required',
                'image' => 'required',
            ], [
                'title.required' => 'Title is required',
                'image.required' => 'Image is required',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'errors' =>  $e->validator->errors(),
                'message' => 'Validation error'
            ], 500);
        }

        return true;
    }

    protected function uploadImage(Request $_request, $_file_name)
    {
        if ($_request->hasFile('image')) {
            $image = $_request->file('image');
            if (is_array($image)) {
                $image = $image[0];
            }
            $image_name = $_file_name . '-' . time() . '.' . $image->getClientOriginalExtension();

            $destinationPath = storage_path(STORE_IMAGE);
            $image->move($destinationPath, $image_name);
        } else {
            $image_name = '';
        }

        return $image_name;
    }

    public function gets(Request $request)
    {
        $query = $request->query();
        $per_page = $query['per_page'] ?? 10;
        $page = $query['page'] ?? 1;

        $per_page = $per_page < 1 ? 1 : $per_page;
        $page = $page < 1 ? 1 : $page;
        $offset = ($page - 1) * $per_page;

        try {
            $posts = Post::skip($offset)->take($per_page)->get();

            return response()->json([
                'error' => false,
                'message' => 'Get posts success',
                'rows' => $posts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function get($id)
    {
        try {
            $post = Post::find($id);
            return response()->json([
                'error' => false,
                'message' => 'Get post success',
                'row' => $post
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function add(Request $request)
    {
        $validate = $this->validateRequest($request);
        if ($validate !== true) {
            return $validate;
        }

        try {
            $slug_title = Str::slug($request->input('title'), '-');
            $image_name = $this->uploadImage($request, $slug_title);

            $post = new Post;
            $post->title = $request->input('title');
            $post->image = $image_name;
            $post->content = $request->input('content');
            $post->user_id = $request->input('user_id');
            $post->save();

            return response()->json([
                'error' => false,
                'message' => 'Add post success',
                'row' => $post
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function duplicate($id)
    {
        try {
            $post = Post::find($id);
            $new_post = $post->replicate();
            $new_post->title = $new_post->title . ' - Copy';
            $new_post->save();

            return response()->json([
                'error' => false,
                'message' => 'Duplicate post success',
                'row' => $new_post
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validate = $this->validateRequest($request);
        if ($validate !== true) {
            return $validate;
        }

        try {
            $slug_title = Str::slug($request->input('title'), '-');
            if ($request->hasFile('image')) {
                $old_image = Post::find($id)->image;
                $old_image_path = storage_path(STORE_IMAGE . '/' . $old_image);
                if (file_exists($old_image_path)) {
                    @unlink($old_image_path);
                }
            }
            $image_name = $this->uploadImage($request, $slug_title);

            $post = Post::find($id);
            $post->title = $request->input('title');
            $post->image = $image_name;
            $post->content = $request->input('content');
            $post->user_id = $request->input('user_id');
            $post->save();

            return response()->json([
                'error' => false,
                'message' => 'Update post success',
                'row' => $post
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $post = Post::find($id);
            if ($post) {
                $image_path = storage_path(STORE_IMAGE . '/' . $post->image);
                if (file_exists($image_path)) {
                    @unlink($image_path);
                }

                $post->delete();
                return response()->json([
                    'error' => false,
                    'message' => 'Delete post success'
                ], 200);
            } else {
                throw new \Exception('Post not found');
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteAll()
    {
        try {
            $posts = Post::all();
            foreach ($posts as $post) {
                $image_path = storage_path(STORE_IMAGE . '/' . $post->image);
                if (file_exists($image_path)) {
                    @unlink($image_path);
                }
            }

            Post::truncate();
            return response()->json([
                'error' => false,
                'message' => 'Delete all posts success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
