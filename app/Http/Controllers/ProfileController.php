<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\DeliveryPerson;
use App\Models\DeliveryEnterprise;

class ProfileController extends ApiController
{
    /**
     * Voir le profil de l'utilisateur connecté
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $user->load([
            'customer',
            'restaurant',
            'deliveryPerson',
            'deliveryEnterprise',
        ]);
    
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
        ];
    
        switch ($user->role) {
            case 'client':
                if ($user->customer_id && $user->customer) {
                    $data['address'] = $user->customer->address;
                    $data['phone_number'] = $user->customer->phone_number;
                    $data['position'] = $user->customer->position;
                }
                break;
    
            case 'partenaire':
                if ($user->restaurant_id && $user->restaurant) {
                    $data['address'] = $user->restaurant->address;
                    $data['phone_number'] = $user->restaurant->phone_number;
                    $data['logo'] = $user->restaurant->logo ? asset('storage/' . $user->restaurant->logo) : null;
                }
                break;
    
            case 'livreur':
                if ($user->delivery_person_id && $user->deliveryPerson) {
                    $data['phone_number'] = $user->deliveryPerson->phone_number;
                    $data['position'] = $user->deliveryPerson->position;
                    $data['avatar'] = $user->deliveryPerson->avatar ? asset('storage/' . $user->deliveryPerson->avatar) : null;
                }
                break;
    
            case 'livreur_entreprise':
                if ($user->delivery_enterprise_id && $user->deliveryEnterprise) {
                    $data['address'] = $user->deliveryEnterprise->address;
                    $data['phone_number'] = $user->deliveryEnterprise->phone_number;
                    $data['logo'] = $user->deliveryEnterprise->logo ? asset('storage/' . $user->deliveryEnterprise->logo) : null;
                    $data['numero_IFU'] = $user->deliveryEnterprise->numero_IFU;
                }
                break;
        }
    
        return $this->successResponse($data, "Profil récupéré avec succès");
    }
    


    /**
     * Mettre à jour le profil de l'utilisateur connecté
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Validation des champs communs
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Champs selon rôle
            'phone_number' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
            'numero_IFU' => 'sometimes|integer',
        ]);

        try {
            // Upload avatar
            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $user->avatar = $request->file('avatar')->store('avatars', 'public');
            }

            // Mise à jour de la table users
            if (isset($validated['name'])) $user->name = $validated['name'];
            if (isset($validated['email'])) $user->email = $validated['email'];
            if (!empty($validated['password'])) $user->password = Hash::make($validated['password']);
            $user->save();

            // Mise à jour des tables spécifiques selon rôle
            switch ($user->role) {
                case 'client':
                    $model = Customer::find($user->customer_id);
                    break;
                case 'partenaire':
                    $model = Restaurant::find($user->restaurant_id);
                    break;
                case 'livreur':
                    $model = DeliveryPerson::find($user->delivery_person_id);
                    break;
                case 'livreur_entreprise':
                    $model = DeliveryEnterprise::find($user->delivery_enterprise_id);
                    break;
                default:
                    $model = null;
                    break;
            }

            if ($model) {
                if (isset($validated['name'])) $model->name = $validated['name'];
                if (isset($validated['email'])) $model->email = $validated['email'];
                if (isset($validated['phone_number'])) $model->phone_number = $validated['phone_number'];
                if (isset($validated['address'])) $model->address = $validated['address'];
                if (isset($validated['numero_IFU']) && property_exists($model, 'numero_IFU')) {
                    $model->numero_IFU = $validated['numero_IFU'];
                }

                if (isset($user->avatar) && property_exists($model, 'avatar')) {
                    $model->avatar = $user->avatar;
                }
                if (isset($user->avatar) && property_exists($model, 'logo')) {
                    $model->logo = $user->avatar;
                }

                $model->save();
            }

            return $this->successResponse($user->fresh(), "Profil mis à jour avec succès");
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la mise à jour du profil : " . $e->getMessage(), 500);
        }
    }
}
