<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Promotor\PromotorProfileResource;
use App\Http\Resources\Api\Promotor\PromotorStatisticsResource;
use App\Http\Resources\Api\FollowerResource;
use App\Models\PromotorDetail;
use App\Models\Follower;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PromotorProfileController extends Controller
{
  /**
   * Get promotor profile
   */
  public function show()
  {
    try {
      $promotor = Auth::user();

      // Check if user is promotor
      if ($promotor->user_type !== 'promotor') {
        return response()->json([
          'status' => 'error',
          'message' => 'User is not a promotor'
        ], 403);
      }

      // Get or create promotor details
      $promotorDetail = PromotorDetail::firstOrCreate(
        ['user_id' => $promotor->id],
        [
          'company_name' => null,
          'description' => null,
          'website' => null,
          'social_media' => null,
          'verification_status' => 'pending',
          'verification_document' => null
        ]
      );

      return new PromotorProfileResource($promotor);
    } catch (\Exception $e) {
      Log::error('Error in get promotor profile: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get promotor profile'
      ], 500);
    }
  }

  /**
   * Update promotor profile
   */
  public function update(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'company_name' => 'sometimes|required|string|max:255',
        'description' => 'sometimes|required|string',
        'website' => 'sometimes|nullable|url|max:255',
        'social_media' => 'sometimes|nullable|array',
        'social_media.*' => 'string',
        'verification_document' => 'sometimes|nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation failed',
          'errors' => $validator->errors()
        ], 422);
      }

      $promotor = Auth::user();

      // Check if user is promotor
      if ($promotor->user_type !== 'promotor') {
        return response()->json([
          'status' => 'error',
          'message' => 'User is not a promotor'
        ], 403);
      }

      // Get existing promotor details or create new one
      $promotorDetail = PromotorDetail::where('user_id', $promotor->id)->first();

      if (!$promotorDetail) {
        $promotorDetail = new PromotorDetail();
        $promotorDetail->user_id = $promotor->id;
        $promotorDetail->verification_status = 'pending';
      }

      // Update promotor details
      if ($request->has('company_name')) {
        $promotorDetail->company_name = $request->company_name;
      }
      if ($request->has('description')) {
        $promotorDetail->description = $request->description;
      }
      if ($request->has('website')) {
        $promotorDetail->website = $request->website;
      }
      if ($request->has('social_media')) {
        $promotorDetail->social_media = $request->social_media;
      }

      // Handle verification document upload
      if ($request->hasFile('verification_document')) {
        // Delete old document if exists
        if ($promotorDetail->verification_document) {
          Storage::disk('public')->delete($promotorDetail->verification_document);
        }

        $document = $request->file('verification_document');
        $documentPath = $document->store('verification-documents', 'public');
        $promotorDetail->verification_document = $documentPath;
        $promotorDetail->verification_status = 'pending';
      }

      $promotorDetail->save();

      return new PromotorProfileResource($promotor);
    } catch (\Exception $e) {
      Log::error('Error in update promotor profile: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update promotor profile'
      ], 500);
    }
  }

  /**
   * Get promotor followers
   */
  public function getFollowers()
  {
    try {
      $promotor = Auth::user();

      // Check if user is promotor
      if ($promotor->user_type !== 'promotor') {
        return response()->json([
          'status' => 'error',
          'message' => 'User is not a promotor'
        ], 403);
      }

      $followers = Follower::where('promotor_id', $promotor->id)
        ->with(['user'])
        ->paginate(10);

      return FollowerResource::collection($followers);
    } catch (\Exception $e) {
      Log::error('Error in get promotor followers: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get promotor followers'
      ], 500);
    }
  }

  /**
   * Get promotor statistics
   */
  public function getStatistics()
  {
    try {
      $promotor = Auth::user();

      // Check if user is promotor
      if ($promotor->user_type !== 'promotor') {
        return response()->json([
          'status' => 'error',
          'message' => 'User is not a promotor'
        ], 403);
      }

      // Get total followers
      $totalFollowers = Follower::where('promotor_id', $promotor->id)->count();

      // Get total events
      $totalEvents = Event::where('promotor_id', $promotor->id)->count();

      // Get total attendees
      $totalAttendees = Event::where('promotor_id', $promotor->id)
        ->withCount('attendees')
        ->get()
        ->sum('attendees_count');

      $statistics = [
        'total_followers' => $totalFollowers,
        'total_events' => $totalEvents,
        'total_attendees' => $totalAttendees
      ];

      return new PromotorStatisticsResource($statistics);
    } catch (\Exception $e) {
      Log::error('Error in get promotor statistics: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get promotor statistics'
      ], 500);
    }
  }
}
