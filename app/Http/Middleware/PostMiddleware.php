<?php

namespace App\Http\Middleware;

use App\Http\Controllers\FileManagementController;
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
        $json_data = $request->json()->all();

        // check category exists
        $category_id = $json_data['category_id'] ?? 0;
        if ($category_id > 0 && !\App\Category::find($category_id)) {
            return response()->json([
                'error' => true,
                'message' => 'Category not found'
            ], 500);
        }

        // copy image from store/temp to store/images
        $image = $json_data['image'] ?? '';
        if ($image) {
            $result = FileManagementController::copyFileTemp($image);
            $result = json_decode($result->getContent(), true);
            if ($result['error']) {
                $request->merge(['image' => $image]);
            } else {
                $request->merge(['image' => $result['data']]);
            }
        }

        FileManagementController::deleteFileTemp();

        return $next($request);
    }
}
