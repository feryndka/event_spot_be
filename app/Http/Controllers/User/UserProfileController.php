<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserProfileController extends Controller
{
    /**
     * Get user profile data
     */
    public function show()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number, // Ubah dari phone ke phone_number
                'bio' => $user->bio, // Tambah bio
                'profile_picture' => $user->profile_picture ? Storage::url($user->profile_picture) : null, // Ubah dari avatar ke profile_picture
                'member_since' => $user->created_at->format('F Y'),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone_number' => 'nullable|string|max:20', // Ubah dari phone ke phone_number
            'bio' => 'nullable|string|max:500', // Tambah bio dengan max 500 karakter
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number, // Ubah dari phone ke phone_number
            'bio' => $request->bio, // Tambah bio
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number, // Ubah dari phone ke phone_number
                'bio' => $user->bio, // Tambah bio
                'profile_picture' => $user->profile_picture ? Storage::url($user->profile_picture) : null, // Ubah dari avatar ke profile_picture
            ]
        ]);
    }

    /**
     * Update profile picture
     */
    public function updateProfilePicture(Request $request) // Ubah nama method dari updateAvatar ke updateProfilePicture
    {
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048' // Ubah dari avatar ke profile_picture
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Delete old profile picture if exists
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Store new profile picture
        $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public'); // Ubah folder dari avatars ke profile_pictures
        
        $user->update(['profile_picture' => $profilePicturePath]); // Ubah dari avatar ke profile_picture

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully',
            'data' => [
                'profile_picture' => Storage::url($profilePicturePath) // Ubah dari avatar ke profile_picture
            ]
        ]);
    }

    /**
     * Delete profile picture
     */
    public function deleteProfilePicture() // Ubah nama method dari deleteAvatar ke deleteProfilePicture
    {
        try {
            $user = Auth::user();

            if ($user->profile_picture) {
                $profilePicturePath = $user->profile_picture;
                
                // Delete physical file from storage
                if (Storage::disk('public')->exists($profilePicturePath)) {
                    Storage::disk('public')->delete($profilePicturePath);
                }

                // Force update database using raw query
                $updated = DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'profile_picture' => null, // Ubah dari avatar ke profile_picture
                        'updated_at' => now()
                    ]);

                if ($updated) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Profile picture deleted successfully'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to update database'
                    ], 500);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'No profile picture to delete'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting profile picture: ' . $e->getMessage()
            ], 500);
        }
    }
}