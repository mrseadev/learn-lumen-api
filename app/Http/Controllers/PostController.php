<?php

namespace App\Http\Controllers;

use App\Category;
use App\User;
use App\Post;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

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

    protected function validateTransactionRequest(Request $request)
    {
        try {
            $this->validate($request, [
                'title' => 'required',
                'image' => 'required',
                'category_name' => 'required',
            ], [
                'title.required' => 'Title is required',
                'image.required' => 'Image is required',
                'category_name.required' => 'Category name is required',
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

    public function gets(Request $request)
    {
        $query = $request->query();
        $perPage = $query['per_page'] ?? 10;
        $page = $query['page'] ?? 1;

        $perPage = $perPage < 1 ? 1 : $perPage;
        $page = $page < 1 ? 1 : $page;
        $offset = ($page - 1) * $perPage;

        try {
            $posts = Post::skip($offset)->take($perPage)->get();

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
            $jsonData = $request->json()->all();
            $post = Post::create($jsonData);

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
            $newPost = $post->replicate();
            $newPost->title = $newPost->title . ' - Copy';
            $newPost->save();

            return response()->json([
                'error' => false,
                'message' => 'Duplicate post success',
                'row' => $newPost
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
            $post = Post::find($id);
            $oldImage = $post->image;
            $jsonData = $request->json()->all();

            $newImage = $jsonData['image'] ?? '';
            if ($newImage !== "" && $oldImage !== "" && $oldImage !== $newImage) {
                FileManagementController::deleteFile($oldImage);
            }

            $post->update($jsonData);

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
                FileManagementController::deleteFile($post->image);

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
                FileManagementController::deleteFile($post->image);
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

    public function addWithTransaction(Request $request)
    {
        $validate = $this->validateTransactionRequest($request);
        if ($validate !== true) {
            return $validate;
        }

        try {
            DB::beginTransaction();

            $jsonData = $request->json()->all();

            $category = new Category();
            $category->name = $jsonData['category_name'];
            $category->save();

            $category_id = $category->id;

            $post = new Post;
            $post->title = $jsonData['title'];
            $post->content = $jsonData['content'];
            $post->user_id = $jsonData['user_id'];
            $post->category_id = $category_id;
            $post->save();

            DB::commit();

            return response()->json([
                'error' => false,
                'message' => 'Add post success',
                'row' => $post
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
