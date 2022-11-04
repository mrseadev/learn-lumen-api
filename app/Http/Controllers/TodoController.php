<?php

namespace App\Http\Controllers;

use App\Todo;

use Illuminate\Http\Request;

class TodoController extends Controller
{
    protected function validateRequest(Request $request)
    {
        try {
            $this->validate($request, [
                'title' => 'required',
            ], [
                'title.required' => 'Title is required',
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

    public function index(Request $request)
    {
        $query = $request->query();
        $per_page = $query['per_page'] ?? 10;
        $page = $query['page'] ?? 1;

        $per_page = $per_page < 1 ? 1 : $per_page;
        $page = $page < 1 ? 1 : $page;
        $offset = ($page - 1) * $per_page;

        try {
            $results = Todo::skip($offset)->take($per_page)->get();
            return response()->json([
                'error' => false,
                'message' => 'Todos fetched successfully',
                'rows' => $results
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
            $result = Todo::find($id);
            if ($result) {
                return response()->json([
                    'error' => false,
                    'message' => 'Todo fetched successfully',
                    'row' => $result
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Todo not found'
                ], 404);
            }
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
            $json_data = $request->json()->all();
            $row = Todo::create($json_data);
            return response()->json([
                'error' => false,
                'message' => 'Todo created successfully',
                'row' => $row
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
            $json_data = $request->json()->all();
            $row = Todo::find($id);
            if (!$row) {
                throw new \Exception('Todo not found');
            }
            $row->update($json_data);
            return response()->json([
                'error' => false,
                'message' => 'Todo updated successfully',
                'row' => $row
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function duplicate(Request $request, $id)
    {
        try {
            $row = Todo::find($id);
            if (!$row) {
                throw new \Exception('Todo not found');
            }
            $row->id = null;
            $row->title = $row->title . ' (copy)';
            $row->save();
            return response()->json([
                'error' => false,
                'message' => 'Todo duplicated successfully',
                'row' => $row
            ], 200);
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
            $row = Todo::find($id);
            if (!$row) {
                throw new \Exception('Todo not found');
            }
            $row->delete();
            return response()->json([
                'error' => false,
                'message' => 'Todo deleted successfully',
                'row' => $row
            ], 200);
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
            $rows = Todo::all();
            foreach ($rows as $row) {
                $row->delete();
            }
            return response()->json([
                'error' => false,
                'message' => 'Todos deleted successfully',
                'rows' => $rows
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
