<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventTag;
use App\Http\Resources\Api\EventTagResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminEventTagController extends Controller
{
  public function index()
  {
    try {
      $tags = EventTag::withCount('events')->get();
      if (request()->segment(1) == 'api') return EventTagResource::collection($tags);
      return EventTagResource::collection($tags);
    } catch (\Exception $e) {
      Log::error('Error in AdminEventTagController@index: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to fetch event tags',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function store(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255|unique:event_tags',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation error',
          'errors' => $validator->errors()
        ], 422);
      }

      DB::beginTransaction();

      $tag = EventTag::create([
        'name' => $request->name,
        'slug' => Str::slug($request->name)
      ]);

      DB::commit();

      return new EventTagResource($tag);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error in AdminEventTagController@store: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to create event tag',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function show($id)
  {
    try {
      $tag = EventTag::find($id);

      if (!$tag) {
        return response()->json([
          'status' => 'error',
          'message' => 'Event tag not found'
        ], 404);
      }

      $tag->load('events');
      return new EventTagResource($tag);
    } catch (\Exception $e) {
      Log::error('Error in AdminEventTagController@show: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to fetch event tag details',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function update(Request $request, $id)
  {
    try {
      $tag = EventTag::find($id);

      if (!$tag) {
        return response()->json([
          'status' => 'error',
          'message' => 'Event tag not found'
        ], 404);
      }

      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255|unique:event_tags,name,' . $id,
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation error',
          'errors' => $validator->errors()
        ], 422);
      }

      DB::beginTransaction();

      Log::info('Updating tag', [
        'tag_id' => $id,
        'old_name' => $tag->name,
        'new_name' => $request->name
      ]);

      $updated = $tag->update([
        'name' => $request->name,
        'slug' => Str::slug($request->name)
      ]);

      if (!$updated) {
        throw new \Exception('Failed to update tag in database');
      }

      DB::commit();

      // Refresh the model to get the latest data
      $tag->refresh();

      return new EventTagResource($tag);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error in AdminEventTagController@update: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update event tag',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function destroy($id)
  {
    try {
      $tag = EventTag::find($id);

      if (!$tag) {
        return response()->json([
          'status' => 'error',
          'message' => 'Event tag not found'
        ], 404);
      }

      DB::beginTransaction();

      Log::info('Deleting tag', [
        'tag_id' => $id,
        'tag_name' => $tag->name
      ]);

      // Delete related records in event_tag_relations
      $detached = $tag->events()->detach();
      Log::info('Detached relations', ['detached_count' => $detached]);

      // Delete the tag
      $deleted = $tag->delete();

      if (!$deleted) {
        throw new \Exception('Failed to delete tag from database');
      }

      DB::commit();

      Log::info('Tag deleted successfully', [
        'tag_id' => $id,
        'tag_name' => $tag->name
      ]);

      return response()->json([
        'status' => 'success',
        'message' => 'Event tag deleted successfully'
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error in AdminEventTagController@destroy: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to delete event tag',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }
}
