<?php

namespace App\Http\Middleware;

use Closure;

class PostMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // check category exists
        $category_id = $request->input('category_id') ?? 0;
        if ($category_id > 0 && !\App\Category::find($category_id)) {
            return response()->json([
                'error' => true,
                'message' => 'Category not found'
            ], 500);
        }

        return $next($request);
    }
}
