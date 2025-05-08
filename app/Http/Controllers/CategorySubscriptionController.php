<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategorySubscriptionController extends Controller
{
  public function store(Request $request, Category $category)
  {
    // Check if already subscribed
    if ($category->subscribers()->where('user_id', Auth::id())->exists()) {
      return response()->json([
        'message' => 'Already subscribed to this category'
      ], 400);
    }

    $subscription = $category->subscribers()->create([
      'user_id' => Auth::id()
    ]);

    return response()->json($subscription, 201);
  }

  public function destroy(Category $category)
  {
    $subscription = $category->subscribers()->where('user_id', Auth::id())->first();

    if (!$subscription) {
      return response()->json([
        'message' => 'Not subscribed to this category'
      ], 404);
    }

    $subscription->delete();

    return response()->json(null, 204);
  }
}
