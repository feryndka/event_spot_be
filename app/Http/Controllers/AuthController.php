<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Http\Resources\Api\UserResource;
use App\Http\Resources\Api\AuthResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AuthController extends Controller
{
  use AuthorizesRequests;

  public function register(Request $request)
  {
    // Validasi input
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:8|confirmed',
      'phone_number' => 'nullable|string|max:20',
      'user_type' => 'nullable|in:admin,user,promotor',
    ]);

    // Cek validasi
    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'message' => 'Validation error',
        'errors' => $validator->errors()
      ], 422);
    }

    try {
      // Buat user baru
      $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'phone_number' => $request->phone_number,
        'user_type' => $request->user_type ?? 'user', // Set default to 'user' if not provided
        'is_verified' => ($request->user_type ?? 'user') === 'user', // Auto verify regular users
        'is_active' => true,
      ]);

      // Generate token
      $token = $user->createToken('auth_token')->plainTextToken;

      // Return response
      return response()->json([
        'status' => 'success',
        'message' => 'User registered successfully',
        'data' => [
          'user' => new UserResource($user),
          'token' => $token,
          'token_type' => 'Bearer'
        ]
      ], 201);
    } catch (\Exception $e) {
      Log::error('Registration error: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to create user account',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function login(Request $request)
  {
    // Validasi input
    $validator = Validator::make($request->all(), [
      'email' => 'required|string|email',
      'password' => 'required|string',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'message' => 'Validation error',
        'errors' => $validator->errors()
      ], 422);
    }

    try {
      // Coba login
      if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
          'status' => 'error',
          'message' => 'Invalid credentials'
        ], 401);
      }

      // Ambil user yang berhasil login
      $user = User::where('email', $request->email)->firstOrFail();

      // Check if user is active
      if (!$user->is_active) {
        return response()->json([
          'status' => 'error',
          'message' => 'Your account has been suspended'
        ], 403);
      }

      // Generate token
      $token = $user->createToken('auth_token')->plainTextToken;

      // Return response dengan data user dan token
      return new AuthResource([
        'user' => $user,
        'token' => $token
      ]);
    } catch (\Exception $e) {
      Log::error('Login error: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to login',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function logout(Request $request)
  {
    try {
      $request->user()->currentAccessToken()->delete();
      return response()->json([
        'status' => 'success',
        'message' => 'Logged out successfully'
      ]);
    } catch (\Exception $e) {
      Log::error('Logout error: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to logout',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function me(Request $request)
  {
    try {
      return new UserResource($request->user());
    } catch (\Exception $e) {
      Log::error('Get user error: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get user data',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function forgotPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|exists:users,email',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'message' => 'Validation error',
        'errors' => $validator->errors()
      ], 422);
    }

    try {
      $status = Password::sendResetLink(
        $request->only('email')
      );

      if ($status === Password::RESET_LINK_SENT) {
        return response()->json([
          'status' => 'success',
          'message' => 'Password reset link sent to your email'
        ]);
      }

      return response()->json([
        'status' => 'error',
        'message' => 'Unable to send password reset link'
      ], 400);
    } catch (\Exception $e) {
      Log::error('Forgot password error: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to process password reset request',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function resetPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'message' => 'Validation error',
        'errors' => $validator->errors()
      ], 422);
    }

    try {
      $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
          $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
          ])->save();

          event(new PasswordReset($user));
        }
      );

      if ($status === Password::PASSWORD_RESET) {
        return response()->json([
          'status' => 'success',
          'message' => 'Password reset successfully'
        ]);
      }

      return response()->json([
        'status' => 'error',
        'message' => 'Unable to reset password'
      ], 400);
    } catch (\Exception $e) {
      Log::error('Reset password error: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to reset password',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function createFirstAdmin(Request $request)
  {
    try {
      // Check if admin already exists
      if (User::where('user_type', 'admin')->exists()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Admin user already exists'
        ], 400);
      }

      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'phone_number' => 'required|string|max:20'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status' => 'error',
          'message' => 'Validation error',
          'errors' => $validator->errors()
        ], 422);
      }

      $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'phone_number' => $request->phone_number,
        'user_type' => 'admin',
        'is_verified' => true,
        'is_active' => true
      ]);

      $token = $user->createToken('auth_token')->plainTextToken;

      return response()->json([
        'status' => 'success',
        'message' => 'Admin user created successfully',
        'data' => [
          'user' => $user,
          'token' => $token,
          'token_type' => 'Bearer'
        ]
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to create admin user'
      ], 500);
    }
  }
}
