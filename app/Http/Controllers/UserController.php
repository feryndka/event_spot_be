<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
  public function profile()
  {
    $user = Auth::user()->load(['preferences', 'promotorDetails']);
    return response()->json($user);
  }

  public function updateProfile(Request $request)
  {
    $user = Auth::user();

    $request->validate([
      'name' => 'required|string|max:255',
      'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
      'phone_number' => 'nullable|string|max:20',
      'bio' => 'nullable|string|max:1000'
    ]);

    $user->update($request->only(['name', 'email', 'phone_number', 'bio']));

    return response()->json($user);
  }

  public function updatePassword(Request $request)
  {
    $request->validate([
      'current_password' => 'required|current_password',
      'password' => 'required|string|min:8|confirmed'
    ]);

    Auth::user()->update([
      'password' => Hash::make($request->password)
    ]);

    return response()->json(['message' => 'Password updated successfully']);
  }

  public function updateAvatar(Request $request)
  {
    $request->validate([
      'avatar' => 'required|image|max:2048' // max 2MB
    ]);

    $user = Auth::user();

    // Delete old avatar if exists
    if ($user->profile_picture) {
      Storage::delete($user->profile_picture);
    }

    // Store new avatar
    $path = $request->file('avatar')->store('avatars', 'public');

    $user->update([
      'profile_picture' => $path
    ]);

    return response()->json([
      'message' => 'Avatar updated successfully',
      'profile_picture' => $path
    ]);
  }
}
