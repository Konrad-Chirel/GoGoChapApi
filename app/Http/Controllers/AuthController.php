<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\DeliveryPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\DeliveryEnterprise;


class AuthController extends ApiController
{
    public function registerClient(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        // Upload de l'image
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
    
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'client',
            'avatar' => $avatarPath,
        ]);
    
        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'address' => $validated['address'],
            'password' => $user->password,
            'avatar' => $avatarPath,
            'position' => '',
        ]);
    
        $user->customer_id = $customer->id;
        $user->save();
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], "Inscription client réussie");
    }
    

    

    public function registerRestaurant(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        // Upload de l'image
        $logoPath = $request->file('logo')->store('logos', 'public');
    
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'partenaire',
            'logo' => $logoPath,
        ]);
    
        $restaurant = Restaurant::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'address' => $validated['address'],
            'password' => $user->password,
            'logo' => $logoPath,
        ]);
    
        $user->restaurant_id = $restaurant->id;
        $user->save();
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], "Inscription restaurant réussie");
    }
    

    public function registerDeliveryPerson(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'required|string|max:255',
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        // Upload de l'image
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
    
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'livreur',
            'avatar' => $avatarPath,
        ]);
    
        $livreur = DeliveryPerson::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => $user->password,
            'avatar' => $avatarPath,
            'files' => '',
            'position' => '',
        ]);
    
        $user->delivery_person_id = $livreur->id;
        $user->save();
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], "Inscription livreur réussie");
    }
    
    public function registerDeliveryEnterprise(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'numero_IFU' => 'required|integer',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        // Upload de l'image
        $logoPath = $request->file('logo')->store('logos', 'public');
    
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'livreur_entreprise',
            'logo' => $logoPath,
        ]);
    
        $enterprise = DeliveryEnterprise::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'address' => $validated['address'],
            'password' => $user->password,
            'logo' => $logoPath,
            'numero_IFU' => $validated['numero_IFU'],
        ]);
    
        $user->delivery_enterprise_id = $enterprise->id;
        $user->save();
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], "Inscription entreprise de livraison réussie");
    }
    

    /**
     * Connexion d'un utilisateur et génération du token.
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string'
            ]);

            $user = User::where('email', $credentials['email'])->first();

            if ($user->is_blocked) {
                return $this->errorResponse("Votre compte a été bloqué par l'administrateur.", 403);
            }

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return $this->errorResponse("Identifiants invalides", 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
            ], "Connexion réussie !");
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la connexion : " . $e->getMessage(), 500);
        }
    }

    /**
     * Déconnexion d’un utilisateur (suppression des tokens).
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return $this->successResponse(null, "Déconnexion réussie.");
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la déconnexion : " . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les infos de l'utilisateur connecté.
     */
    public function me(Request $request)
    {
        return $this->successResponse($request->user());
    }
}
