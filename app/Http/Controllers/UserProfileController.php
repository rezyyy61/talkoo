<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\UserProfile;

class UserProfileController extends Controller
{
    /**
     * Fetch the authenticated user's profile.
     */
    public function getUser(Request $request)
    {
        $user = $request->user()->load('profile');
        return response()->json(['user' => $user]);
    }

    /**
     * Update the authenticated user's profile.
     *
     * `avatarImage` is used for an uploaded image.
     * `avatarColor` is used for a background color (e.g., #FF00FF).
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        $validatedData = $request->validate([
            'name'        => ['string', 'max:255'],
            'userId'      => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('user_profiles')->ignore($profile->id),
            ],
            'bio'         => ['nullable', 'string', 'max:1000'],
            'avatarImage' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'avatarColor' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            'removeAvatar' => ['sometimes'],
        ]);

        // Convert removeAvatar string -> boolean
        $removeAvatar = $request->boolean('removeAvatar');

        if ($removeAvatar) {
            // Delete old file if it still exists
            if ($this->isImage($profile->avatarImage) && Storage::disk('public')->exists($profile->avatarImage)) {
                Storage::disk('public')->delete($profile->avatarImage);
            }
            $profile->avatarImage = null;
        }

        // Update user table
        $user->name = $validatedData['name'] ?? $user->name;
        $user->save();

        // Update user_profiles
        $profile->userId = $validatedData['userId'] ?? $profile->userId;
        $profile->bio = $validatedData['bio'] ?? $profile->bio;

        // Handle avatarImage upload
        if ($request->hasFile('avatarImage')) {
            // Delete old image if exists
            if ($this->isImage($profile->avatarImage) && Storage::disk('public')->exists($profile->avatarImage)) {
                Storage::disk('public')->delete($profile->avatarImage);
            }
            // Store new image and update avatarImage field
            $path = $request->file('avatarImage')->store('avatars', 'public');
            $profile->avatarImage = $path;
        }
        if ($validatedData['removeAvatar'] ?? false) {
            if ($this->isImage($profile->avatarImage) && Storage::disk('public')->exists($profile->avatarImage)) {
                Storage::disk('public')->delete($profile->avatarImage);
            }
            $profile->avatarImage = null;
        }

        // Handle avatarColor update
        if (!empty($validatedData['avatarColor'])) {
            $profile->avatarColor = $validatedData['avatarColor'];
        }

        $profile->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->load('profile'),
        ]);
    }

    /**
     * Helper to check if the current avatarImage is an image path.
     *
     * @param string|null $path
     * @return bool
     */
    private function isImage($path)
    {
        if (!$path) {
            return false;
        }

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($extension, $imageExtensions);
    }
}
