<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\EventImageResource;

class PromotorEventImageController extends Controller
{
  /**
   * Store a new event image
   */
  public function store(Request $request, Event $event)
  {
    try {
      // Check if user is the event promotor
      if ($event->promotor_id !== Auth::id()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized access'
        ], 403);
      }

      $validator = Validator::make($request->all(), [
        'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'image_type' => 'required|in:additional,gallery',
        'is_primary' => 'boolean'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      // Handle image upload
      $image = $request->file('image');
      $path = $image->store('events/images', 'public');

      // Create image record
      $eventImage = $event->images()->create([
        'image_path' => $path,
        'image_type' => $request->image_type,
        'is_primary' => $request->is_primary ?? false,
        'order' => $event->images()->count() + 1
      ]);

      // If this is primary, unset other primary images
      if ($eventImage->is_primary) {
        $event->images()
          ->where('id', '!=', $eventImage->id)
          ->where('is_primary', true)
          ->update(['is_primary' => false]);
      }

      return new EventImageResource($eventImage);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to upload image'
      ], 500);
    }
  }

  /**
   * Update an event image
   */
  public function update(Request $request, Event $event, EventImage $image)
  {
    try {
      // Check if user is the event promotor
      if ($event->promotor_id !== Auth::id()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized access'
        ], 403);
      }

      // Check if image belongs to event
      if ($image->event_id !== $event->id) {
        return response()->json([
          'status' => 'error',
          'message' => 'Image not found'
        ], 404);
      }

      $validator = Validator::make($request->all(), [
        'image' => 'sometimes|required|image|mimes:jpeg,png,jpg|max:2048',
        'image_type' => 'sometimes|required|in:additional,gallery',
        'is_primary' => 'boolean'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      // Handle image upload if new image is provided
      if ($request->hasFile('image')) {
        // Delete old image
        Storage::disk('public')->delete($image->image_path);

        $newImage = $request->file('image');
        $path = $newImage->store('events/images', 'public');
        $image->image_path = $path;
      }

      // Update image record
      if ($request->has('image_type')) {
        $image->image_type = $request->image_type;
      }

      if ($request->has('is_primary')) {
        $image->is_primary = $request->is_primary;

        // If this is primary, unset other primary images
        if ($image->is_primary) {
          $event->images()
            ->where('id', '!=', $image->id)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
        }
      }

      $image->save();

      return new EventImageResource($image);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update image'
      ], 500);
    }
  }

  /**
   * Delete an event image
   */
  public function destroy(Event $event, EventImage $image)
  {
    try {
      // Check if user is the event promotor
      if ($event->promotor_id !== Auth::id()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized access'
        ], 403);
      }

      // Check if image belongs to event
      if ($image->event_id !== $event->id) {
        return response()->json([
          'status' => 'error',
          'message' => 'Image not found'
        ], 404);
      }

      // Delete image file
      Storage::disk('public')->delete($image->image_path);

      // Delete image record
      $image->delete();

      return response()->json([
        'status' => 'success',
        'message' => 'Image deleted successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to delete image'
      ], 500);
    }
  }

  /**
   * Reorder event images
   */
  public function reorder(Request $request, Event $event, EventImage $image)
  {
    try {
      // Check if user is the event promotor
      if ($event->promotor_id !== Auth::id()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Unauthorized access'
        ], 403);
      }

      // Check if image belongs to event
      if ($image->event_id !== $event->id) {
        return response()->json([
          'status' => 'error',
          'message' => 'Image not found'
        ], 404);
      }

      $validator = Validator::make($request->all(), [
        'new_order' => 'required|integer|min:1'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      $newOrder = $request->new_order;
      $oldOrder = $image->order;

      if ($newOrder > $oldOrder) {
        // Moving down
        $event->images()
          ->where('order', '>', $oldOrder)
          ->where('order', '<=', $newOrder)
          ->decrement('order');
      } else {
        // Moving up
        $event->images()
          ->where('order', '>=', $newOrder)
          ->where('order', '<', $oldOrder)
          ->increment('order');
      }

      $image->order = $newOrder;
      $image->save();

      return new EventImageResource($image);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to reorder image'
      ], 500);
    }
  }
}
