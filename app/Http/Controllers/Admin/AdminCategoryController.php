<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\CategoryResource;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

class AdminCategoryController extends Controller
{
  use AuthorizesRequests;

  public function index()
  {
    try {
      $categories = Category::withCount('events')->get();
      return CategoryResource::collection($categories);
    } catch (\Exception $e) {
      Log::error('Error in AdminCategoryController@index: ' . $e->getMessage());
      return response()->json(['message' => 'Failed to fetch categories'], 500);
    }
  }

  public function store(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255|unique:categories',
        'description' => 'nullable|string',
        'icon' => 'nullable|string|max:255',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      $category = Category::create([
        'name' => $request->name,
        'slug' => Str::slug($request->name),
        'description' => $request->description,
        'icon' => $request->icon,
      ]);

      return new CategoryResource($category);
    } catch (\Exception $e) {
      Log::error('Error in AdminCategoryController@store: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to create category',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function show(Category $category)
  {
    try {
      return new CategoryResource($category->load('events'));
    } catch (\Exception $e) {
      Log::error('Error in AdminCategoryController@show: ' . $e->getMessage());
      return response()->json(['message' => 'Failed to fetch category'], 500);
    }
  }

  public function update(Request $request, Category $category)
  {
    try {
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        'description' => 'nullable|string',
        'icon' => 'nullable|string|max:255',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      $category->update([
        'name' => $request->name,
        'slug' => Str::slug($request->name),
        'description' => $request->description,
        'icon' => $request->icon,
      ]);

      return new CategoryResource($category);
    } catch (\Exception $e) {
      Log::error('Error in AdminCategoryController@update: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update category',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function destroy(Category $category)
  {
    try {
      if ($category->events()->count() > 0) {
        return response()->json([
          'status' => 'error',
          'message' => 'Cannot delete category with associated events'
        ], 422);
      }

      $category->delete();
      return response()->json([
        'status' => 'success',
        'message' => 'Category deleted successfully'
      ]);
    } catch (\Exception $e) {
      Log::error('Error in AdminCategoryController@destroy: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to delete category',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}
