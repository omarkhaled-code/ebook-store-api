<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Support\Facades\Hash;

class UserSettingsController extends Controller
{
    // PUT /api/v1/user/profile
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();

         $emailChanged = $user->email !== $request->email;
         $email_verified_at = $emailChanged ? null : $user->email_verified_at;
         
        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            // If email changed — require re-verification
            'email_verified_at' => $email_verified_at,
        ]);

        // Send new verification email if email changed
        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'message' => $emailChanged
                ? 'Profile updated. Please verify your new email.'
                : 'Profile updated successfully.',
            'user' => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'role'              => $user->role,
                'email_verified_at' => $user->email_verified_at,
            ],
        ]);
    }

    // PUT /api/v1/user/password
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth()->user();

        // Check current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
                'errors'  => [
                    'current_password' => ['Current password is incorrect.']
                ]
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Revoke all other tokens — force re-login on other devices
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}
