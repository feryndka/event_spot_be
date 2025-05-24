<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\Api\UserResource;

class AdminUserController extends Controller
{
  public function index(Request $request)
  {
    try {
      $query = User::query()
        ->when($request->search, function ($query, $search) {
          $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
          });
        })
        ->when($request->user_type, function ($query, $userType) {
          $query->where('user_type', $userType);
        })
        ->when($request->status, function ($query, $status) {
          $query->where('status', $status);
        });

      $users = $query->latest()->paginate(10);

      return UserResource::collection($users);
    } catch (\Exception $e) {
      Log::error('Error in user index: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to load users',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function store(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'user_type' => 'required|in:user,promotor,admin',
        'status' => 'required|in:active,inactive,suspended',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation error',
          'errors' => $validator->errors()
        ], 422);
      }

      $data = $validator->validated();
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      return new UserResource($user);
    } catch (\Exception $e) {
      Log::error('Error in user store: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to create user',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function show(User $user)
  {
    try {
      return new UserResource($user);
    } catch (\Exception $e) {
      Log::error('Error in user show: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to load user details',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function update(Request $request, User $user)
  {
    try {
      $validator = Validator::make($request->all(), [
        'name' => 'string|max:255',
        'email' => 'string|email|max:255|unique:users,email,' . $user->id,
        'password' => 'nullable|string|min:8',
        'user_type' => 'in:user,promotor,admin',
        'status' => 'in:active,inactive,suspended',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation error',
          'errors' => $validator->errors()
        ], 422);
      }

      $data = $validator->validated();

      if (isset($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      }

      $user->update($data);

      return new UserResource($user);
    } catch (\Exception $e) {
      Log::error('Error in user update: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update user',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function destroy(User $user)
  {
    try {
      // Prevent deleting the last admin
      if ($user->user_type === 'admin' && User::where('user_type', 'admin')->count() <= 1) {
        return response()->json([
          'status' => 'error',
          'message' => 'Cannot delete the last admin user'
        ], 422);
      }

      $user->delete();

      return response()->json([
        'status' => 'success',
        'message' => 'User deleted successfully'
      ]);
    } catch (\Exception $e) {
      Log::error('Error in user destroy: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to delete user',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function updateActive(Request $request, User $user)
  {
    try {
      $validator = Validator::make($request->all(), [
        'is_active' => 'required|boolean'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation error',
          'errors' => $validator->errors()
        ], 422);
      }

      $user->update(['is_active' => $request->is_active]);

      return new UserResource($user);
    } catch (\Exception $e) {
      Log::error('Error in user active status update: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update user active status',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }

  public function updateRole(Request $request, User $user)
  {
    try {
      $validator = Validator::make($request->all(), [
        'user_type' => 'required|in:user,promotor,admin'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation error',
          'errors' => $validator->errors()
        ], 422);
      }

      // Prevent changing the last admin's role
      if (
        $user->user_type === 'admin' &&
        $request->user_type !== 'admin' &&
        User::where('user_type', 'admin')->count() <= 1
      ) {
        return response()->json([
          'status' => 'error',
          'message' => 'Cannot change the last admin\'s role'
        ], 422);
      }

      $user->update(['user_type' => $request->user_type]);

      return new UserResource($user);
    } catch (\Exception $e) {
      Log::error('Error in user role update: ' . $e->getMessage());
      Log::error('Stack trace: ' . $e->getTraceAsString());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update user role',
        'debug' => config('app.debug') ? $e->getMessage() : null
      ], 500);
    }
  }
}
