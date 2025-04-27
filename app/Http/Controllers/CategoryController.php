<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
  public function index()
  {
    $categories = Category::withCount('events')->get();
    return response()->json($categories);
  }

  public function show(Category $category)
  {
    $category->load('events');
    return response()->json($category);
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255|unique:categories',
      'description' => 'nullable|string',
      'icon' => 'nullable|string',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $category = Category::create([
      'name' => $request->name,
      'slug' => Str::slug($request->name),
      'description' => $request->description,
      'icon' => $request->icon,
    ]);

    return response()->json($category, 201);
  }

  public function update(Request $request, Category $category)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $category->id,
      'description' => 'nullable|string',
      'icon' => 'nullable|string',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $request->all();
    if ($request->has('name')) {
      $data['slug'] = Str::slug($request->name);
    }

    $category->update($data);
    return response()->json($category);
  }

  public function destroy(Category $category)
  {
    if ($category->events()->exists()) {
      return response()->json(['message' => 'Cannot delete category with associated events'], 400);
    }

    $category->delete();
    return response()->json(null, 204);
  }
}
