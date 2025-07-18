<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\DeliveryPerson;
use App\Models\DeliveryEnterprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

class UserController extends ApiController
{
    public function index(Request $request)
    {
        try {
            $role = $request->query('role'); // client, partenaire, livreur, etc.
            $perPage = $request->query('per_page', 10); // nombre d’éléments par page (défaut : 10)
    
            $query = User::query();
    
            if ($role) {
                $query->where('role', $role);
            }
    
            $users = $query->latest()->paginate($perPage);
    
            return $this->successResponse($users);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération des utilisateurs : " . $e->getMessage(), 500);
        }
    }
    
    

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|in:client,partenaire,livreur,livreur_entreprise,admin',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'numero_IFU' => 'nullable|integer'
        ]);
    
        DB::beginTransaction();
    
        try {
            // Upload avatar
            $avatarPath = $request->hasFile('avatar')
                ? $request->file('avatar')->store('avatars', 'public')
                : null;
    
            // Upload logo
            $logoPath = $request->hasFile('logo')
                ? $request->file('logo')->store('logos', 'public')
                : null;
    
            // Création utilisateur principal
            $user = new User();
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->phone_number = $validated['phone_number'] ?? null;
            $user->address = $validated['address'] ?? null;
            $user->password = Hash::make('1234');
            $user->role = $validated['role'];
            $user->avatar = $avatarPath;
    
            // Créer l'entité secondaire selon le rôle
            switch ($validated['role']) {
                case 'client':
                    $customer = Customer::create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'address' => $user->address,
                        'password' => $user->password,
                        'avatar' => $user->avatar,
                        'position' => '',
                    ]);
                    $user->customer_id = $customer->id;
                    break;
    
                case 'partenaire':
                    $restaurant = Restaurant::create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'address' => $user->address,
                        'password' => $user->password,
                        'logo' => $logoPath,
                    ]);
                    $user->restaurant_id = $restaurant->id;
                    break;
    
                    case 'livreur':
                        $livreur = DeliveryPerson::create([
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone_number' => $user->phone_number,
                            'password' => $user->password,
                            'avatar' => $user->avatar,
                            'files' => '',
                            'position' => '',
                        ]);
                        $user->delivery_person_id = $livreur->id;
                        break;
                
                    case 'livreur_entreprise':
                        $entrepriseLivreur = DeliveryEnterprise::create([
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone_number' => $user->phone_number,
                            'address' => $user->address,
                            'password' => $user->password,
                            'logo' => $logoPath,
                            'numero_IFU' => $request->input('numero_IFU'),
                        ]);
                        $user->delivery_enterprise_id = $entrepriseLivreur->id;
                        break;

                    case 'admin':
                        $admin = Admin::create([
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone_number' => $user->phone_number ?? null,
                            'address' => $user->address ?? null,
                                'password' => $user->password,
                                'avatar' => $user->avatar,
                            ]);
                            $user->admin_id = $admin->id;
                            break;
                        
            }
    
            $user->save();
    
            DB::commit();
            return $this->successResponse($user, "Utilisateur ajouté avec succès");
        } catch (\Exception $e) {
            DB::rollBack();
    
            // Supprimer fichiers uploadés en cas d'erreur
            if (!empty($avatarPath)) Storage::disk('public')->delete($avatarPath);
            if (!empty($logoPath)) Storage::disk('public')->delete($logoPath);
    
            return $this->errorResponse("Erreur lors de la création : " . $e->getMessage(), 500);
        }
    }
    

   

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048',
            'logo' => 'nullable|image|max:2048',
            'numero_IFU' => 'nullable|integer', // pour livreur_entreprise si besoin
        ]);
    
        DB::beginTransaction();
    
        try {
            // Gestion upload avatar
            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }
    
            // Gestion upload logo pour partenaires et livreur_entreprise
            if ($request->hasFile('logo') && in_array($user->role, ['partenaire', 'livreur_entreprise'])) {
                if ($user->role === 'partenaire') {
                    $partner = Restaurant::where('email', $user->email)->first();
                    if ($partner && $partner->logo) {
                        Storage::disk('public')->delete($partner->logo);
                    }
                    $logoPath = $request->file('logo')->store('logos', 'public');
                    $partner->update(['logo' => $logoPath]);
                } elseif ($user->role === 'livreur_entreprise') {
                    $enterprise = DeliveryEnterprise::where('email', $user->email)->first();
                    if ($enterprise && $enterprise->logo) {
                        Storage::disk('public')->delete($enterprise->logo);
                    }
                    $logoPath = $request->file('logo')->store('logos', 'public');
                    $enterprise->update(['logo' => $logoPath]);
                }
            }
    
            // Mise à jour de l'utilisateur
            $user->update($validated);
    
            // Mise à jour des données dans les tables liées selon rôle
            switch ($user->role) {
                case 'client':
                    $customer = Customer::find($user->customer_id);
                    if ($customer) {
                        $customer->update(Arr::only($validated, ['name', 'phone_number', 'address', 'avatar']));
                    }
                    break;
            
                case 'partenaire':
                    $restaurant = Restaurant::find($user->restaurant_id);
                    if ($restaurant) {
                        $restaurant->update(Arr::only($validated, ['name', 'phone_number', 'address']));
                    }
                    break;
            
                case 'livreur':
                    $deliveryPerson = DeliveryPerson::find($user->delivery_person_id);
                    if ($deliveryPerson) {
                        $deliveryPerson->update(Arr::only($validated, ['name', 'phone_number', 'avatar']));
                    }
                    break;
            
                case 'livreur_entreprise':
                    $enterprise = DeliveryEnterprise::find($user->delivery_enterprise_id);
                    if ($enterprise) {
                        $enterprise->update(Arr::only($validated, ['name', 'phone_number', 'address', 'numero_IFU']));
                    }
                    break;
            
                case 'admin':
                    $admin = Admin::find($user->admin_id);
                    if ($admin) {
                        $admin->update(Arr::only($validated, ['name', 'phone_number', 'address', 'avatar']));
                    }
                    break;
            }
            
    
            DB::commit();
    
            return $this->successResponse($user, "Utilisateur mis à jour");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse("Erreur de mise à jour : " . $e->getMessage(), 500);
        }
    }
    

    public function destroy(User $user)
    {
        DB::beginTransaction();
    
        try {
            // Suppression de l'avatar du user s'il existe
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
    
            switch ($user->role) {
                case 'client':
                    $customer = Customer::find($user->customer_id);
                    if ($customer) {
                        if ($customer->avatar) {
                            Storage::disk('public')->delete($customer->avatar);
                        }
                        $customer->delete();
                    }
                    break;
    
                case 'partenaire':
                    $restaurant = Restaurant::find($user->restaurant_id);
                    if ($restaurant) {
                        if ($restaurant->logo) {
                            Storage::disk('public')->delete($restaurant->logo);
                        }
                        $restaurant->delete();
                    }
                    break;
    
                case 'livreur':
                    $deliveryPerson = DeliveryPerson::find($user->delivery_person_id);
                    if ($deliveryPerson) {
                        if ($deliveryPerson->avatar) {
                            Storage::disk('public')->delete($deliveryPerson->avatar);
                        }
                        $deliveryPerson->delete();
                    }
                    break;
    
                case 'livreur_entreprise':
                    $enterprise = DeliveryEnterprise::find($user->delivery_enterprise_id);
                    if ($enterprise) {
                        if ($enterprise->logo) {
                            Storage::disk('public')->delete($enterprise->logo);
                        }
                        $enterprise->delete();
                    }
                    break;
    
                case 'admin':
                    $admin = \App\Models\Admin::find($user->admin_id);
                    if ($admin && $admin->avatar) {
                        Storage::disk('public')->delete($admin->avatar);
                    }
                    $admin?->delete();
                    break;
    
                default:
                    throw new \Exception("Rôle utilisateur inconnu : " . $user->role);
            }
    
            $user->delete();
    
            DB::commit();
            return $this->successResponse(null, "Utilisateur supprimé avec succès");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse("Erreur lors de la suppression : " . $e->getMessage(), 500);
        }
    }
    

public function toggleBlock(User $user)
{
    $user->is_blocked = !$user->is_blocked;
    $user->save();

    $status = $user->is_blocked ? 'bloqué' : 'débloqué';
    return $this->successResponse($user, "Utilisateur $status avec succès.");
}



}
