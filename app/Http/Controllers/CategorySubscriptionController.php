<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategorySubscription;
use Illuminate\Http\Request;
use App\Http\Resources\Api\CategorySubscriptionResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategorySubscriptionController extends Controller
{
  /**
   * Subscribe to a category
   */
  public function store(Request $request, Category $category)
  {
    try {
      // Check if already subscribed
      $existingSubscription = CategorySubscription::where('user_id', $request->user()->id)
        ->where('category_id', $category->id)
        ->first();

      if ($existingSubscription) {
        return response()->json([
          'status' => 'error',
          'message' => 'Already subscribed to this category'
        ], 422);
      }

      // Create subscription
      $subscription = CategorySubscription::create([
        'user_id' => $request->user()->id,
        'category_id' => $category->id
      ]);

      return new CategorySubscriptionResource($subscription);
    } catch (\Exception $e) {
      Log::error('Error in category subscription: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to subscribe to category'
      ], 500);
    }
  }

  /**
   * Unsubscribe from a category
   */
  public function destroy(Request $request, Category $category)
  {
    try {
      $subscription = CategorySubscription::where('user_id', $request->user()->id)
        ->where('category_id', $category->id)
        ->first();

      if (!$subscription) {
        return response()->json([
          'status' => 'error',
          'message' => 'Not subscribed to this category'
        ], 404);
      }

      $subscription->delete();

      return response()->json([
        'status' => 'success',
        'message' => 'Successfully unsubscribed from category'
      ]);
    } catch (\Exception $e) {
      Log::error('Error in category unsubscription: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to unsubscribe from category'
      ], 500);
    }
  }
}
