<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\Api\Admin\VerificationResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PromotorVerificationController extends Controller
{
  public function index(Request $request)
  {
    try {
      $query = User::query()
        ->where('user_type', 'promotor')
        ->where('is_verified', false)
        ->with('promotorDetail')
        ->when($request->search, function ($query, $search) {
          $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
          });
        });

      $promotors = $query->latest()->paginate(10);

      return VerificationResource::collection($promotors);
    } catch (\Exception $e) {
      Log::error('Error in promotor verification index: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to load promotor verifications'
      ], 500);
    }
  }

  public function show(User $promotor)
  {
    try {
      // Check if user is a promotor
      if ($promotor->user_type !== 'promotor') {
        return response()->json([
          'status' => 'error',
          'message' => 'User is not a promotor'
        ], 422);
      }

      // Load or create promotor detail
      if (!$promotor->promotorDetail) {
        $promotor->promotorDetail()->create([
          'verification_status' => 'pending',
          'company_name' => null,
          'description' => null,
          'website' => null,
          'verification_document' => null
        ]);
        $promotor->load('promotorDetail');
      }

      return new VerificationResource($promotor);
    } catch (\Exception $e) {
      Log::error('Error in promotor verification show: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to load promotor details'
      ], 500);
    }
  }

  public function approve(Request $request, User $promotor)
  {
    try {
      if ($promotor->user_type !== 'promotor') {
        return response()->json([
          'status' => 'error',
          'message' => 'User is not a promotor'
        ], 422);
      }

      if ($promotor->is_verified) {
        return response()->json([
          'status' => 'error',
          'message' => 'Promotor is already verified'
        ], 422);
      }

      // Update user verification status
      $promotor->update([
        'is_verified' => true
      ]);

      // Update or create promotor detail
      if ($promotor->promotorDetail) {
        $promotor->promotorDetail->update([
          'verification_status' => 'verified'
        ]);
      } else {
        $promotor->promotorDetail()->create([
          'verification_status' => 'verified',
          'company_name' => null,
          'description' => null,
          'website' => null,
          'verification_document' => null
        ]);
      }

      // Reload the promotor with its detail
      $promotor->load('promotorDetail');

      return new VerificationResource($promotor);
    } catch (\Exception $e) {
      Log::error('Error in promotor verification approve: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to approve promotor: ' . $e->getMessage()
      ], 500);
    }
  }

  public function reject(Request $request, User $promotor)
  {
    try {
      if ($promotor->user_type !== 'promotor') {
        return response()->json([
          'status' => 'error',
          'message' => 'User is not a promotor'
        ], 422);
      }

      if ($promotor->is_verified) {
        return response()->json([
          'status' => 'error',
          'message' => 'Promotor is already verified'
        ], 422);
      }

      // Update or create promotor detail
      if ($promotor->promotorDetail) {
        $promotor->promotorDetail->update([
          'verification_status' => 'rejected'
        ]);
      } else {
        $promotor->promotorDetail()->create([
          'verification_status' => 'rejected',
          'company_name' => null,
          'description' => null,
          'website' => null,
          'verification_document' => null
        ]);
      }

      return new VerificationResource($promotor->load('promotorDetail'));
    } catch (\Exception $e) {
      Log::error('Error in promotor verification reject: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to reject promotor: ' . $e->getMessage()
      ], 500);
    }
  }
}
