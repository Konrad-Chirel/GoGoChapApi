<?php

namespace App\Http\Controllers;

use App\Models\DeliveryPerson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DeliveryPersonController extends ApiController
{
    // ✅ Créer un livreur pour une entreprise
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:4',
            'position' => 'required|string',
            'files' => 'required|file',
        ]);

        try {
            $enterprise = $request->user();

            if ($enterprise->role !== 'entreprise_livraison') {
                return $this->errorResponse("Seules les entreprises de livraison peuvent créer des livreurs.", 403);
            }

            // Upload fichier
            $filePath = $request->file('files')->store('livreurs_docs', 'public');

            // Créer livreur dans `delivery_persons`
            $livreur = DeliveryPerson::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'files' => $filePath,
                'position' => $request->position,
                'delivery_enterprise_id' => $enterprise->delivery_enterprise_id,
            ]);

            // Créer user associé
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tel' => $request->phone_number,
                'role' => 'livreur',
                'delivery_person_id' => $livreur->id,
                'delivery_enterprise_id' => $enterprise->delivery_enterprise_id,
            ]);

            return $this->successResponse(['livreur' => $livreur, 'user' => $user], 'Livreur créé avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la création du livreur : " . $e->getMessage(), 500);
        }
    }

    // ✅ Lister les livreurs de l’entreprise connectée
    public function index(Request $request)
    {
        try {
            $enterprise = $request->user();

            if ($enterprise->role !== 'entreprise_livraison') {
                return $this->errorResponse("Accès non autorisé.", 403);
            }

            $livreurs = DeliveryPerson::where('delivery_enterprise_id', $enterprise->delivery_enterprise_id)->get();

            return $this->successResponse($livreurs);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors du chargement des livreurs : " . $e->getMessage(), 500);
        }
    }

    // ✅ Voir un livreur (model binding)
    public function show(Request $request, DeliveryPerson $livreur)
    {
        try {
            $enterprise = $request->user();

            if ($enterprise->role !== 'entreprise_livraison' || $livreur->delivery_enterprise_id !== $enterprise->delivery_enterprise_id) {
                return $this->errorResponse("Accès non autorisé à ce livreur.", 403);
            }

            return $this->successResponse($livreur);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération du livreur : " . $e->getMessage(), 500);
        }
    }

    // ✅ Supprimer un livreur
    public function destroy(Request $request, DeliveryPerson $livreur)
    {
        try {
            $enterprise = $request->user();

            if ($enterprise->role !== 'entreprise_livraison' || $livreur->delivery_enterprise_id !== $enterprise->delivery_enterprise_id) {
                return $this->errorResponse("Suppression non autorisée.", 403);
            }

            // Supprimer fichier associé
            if ($livreur->files) {
                Storage::disk('public')->delete($livreur->files);
            }

            // Supprimer user associé
            User::where('delivery_person_id', $livreur->id)->delete();

            $livreur->delete();

            return $this->successResponse(null, 'Livreur supprimé avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la suppression : " . $e->getMessage(), 500);
        }
    }
}
