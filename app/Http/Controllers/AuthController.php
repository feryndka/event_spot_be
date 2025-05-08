<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:8',
      'user_type' => 'required|in:user,promotor',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = User::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'user_type' => $request->user_type,
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
      'user' => $user,
      'token' => $token,
    ], 201);
  }

  public function login(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email',
      'password' => 'required',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    if (!Auth::attempt($request->only('email', 'password'))) {
      return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user = User::where('email', $request->email)->first();
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
      'user' => $user,
      'token' => $token,
    ]);
  }

  public function logout(Request $request)
  {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out successfully']);
  }

  public function me(Request $request)
  {
    return response()->json($request->user());
  }

  public function forgotPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => 'required|email|exists:users,email',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $status = Password::sendResetLink(
      $request->only('email')
    );

    if ($status === Password::RESET_LINK_SENT) {
      return response()->json(['message' => 'Password reset link sent to your email']);
    }

    return response()->json(['message' => 'Unable to send password reset link'], 400);
  }

  public function resetPassword(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

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
      return response()->json(['message' => 'Password reset successfully']);
    }

    return response()->json(['message' => 'Unable to reset password'], 400);
  }
}
