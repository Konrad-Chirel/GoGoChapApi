<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends ApiController
{
    /**
     * Récupérer les informations du profil administrateur connecté
     */
    public function profile(Request $request)
    {
        try {
            $admin = $request->user();

            if (!$admin || $admin->role !== 'admin') {
                return $this->errorResponse("Accès non autorisé", 403);
            }

            $data = [
                'name' => $admin->name,
                'email' => $admin->email,
                'avatar' => $admin->avatar ? asset('storage/' . $admin->avatar) : null,
                // ajoute d'autres champs si besoin
            ];

            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération du profil : " . $e->getMessage(), 500);
        }
    }

    /**
     * Modifier les informations du profil administrateur
     */
    public function updateProfile(Request $request)
    {
        $admin = $request->user();

        if (!$admin || $admin->role !== 'admin') {
            return $this->errorResponse("Accès non autorisé", 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $admin->id,
            'password' => 'nullable|string|min:6|confirmed', // attendu password_confirmation
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Gérer upload avatar
            if ($request->hasFile('avatar')) {
                if ($admin->avatar) {
                    Storage::disk('public')->delete($admin->avatar);
                }
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $admin->avatar = $avatarPath;
            }

            if (isset($validated['name'])) {
                $admin->name = $validated['name'];
            }

            if (isset($validated['email'])) {
                $admin->email = $validated['email'];
            }

            if (!empty($validated['password'])) {
                $admin->password = Hash::make($validated['password']);
            }

            $admin->save();

            return $this->successResponse($admin, "Profil mis à jour avec succès");
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la mise à jour du profil : " . $e->getMessage(), 500);
        }
    }
}
