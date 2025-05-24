<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\Api\CategoryResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
  use AuthorizesRequests;

  public function index()
  {
    $categories = Category::withCount('events')->get();
    return CategoryResource::collection($categories);
  }

  public function show(Category $category)
  {
    return new CategoryResource($category->load('events'));
  }
}
