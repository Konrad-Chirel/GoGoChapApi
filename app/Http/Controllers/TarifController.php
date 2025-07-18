<?php

namespace App\Http\Controllers;

use App\Models\Tarif;
use Illuminate\Http\Request;

class TarifController extends ApiController
{
    /**
     * Liste tous les tarifs.
     */
    public function index()
    {
        try {
            $tarifs = Tarif::all();
            return $this->successResponse($tarifs);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération des tarifs : " . $e->getMessage(), 500);
        }
    }

    /**
     * Affiche un tarif spécifique.
     */
    public function show(Tarif $tarif)
    {
        try {
            return $this->successResponse($tarif);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération du tarif : " . $e->getMessage(), 500);
        }
    }

    /**
     * Crée un nouveau tarif.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'montant' => 'required|integer|min:0',
            ]);

            $tarif = Tarif::create($validated);

            return $this->successResponse($tarif, "Tarif créé avec succès.", 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse("Données invalides : " . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la création du tarif : " . $e->getMessage(), 500);
        }
    }

    /**
     * Met à jour un tarif existant.
     */
    public function update(Request $request, Tarif $tarif)
    {
        try {
            $validated = $request->validate([
                'titre' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|nullable|string|max:255',
                'montant' => 'required|integer|min:0',
            ]);

            $tarif->update($validated);

            return $this->successResponse($tarif, "Tarif mis à jour avec succès.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse("Données invalides : " . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la mise à jour du tarif : " . $e->getMessage(), 500);
        }
    }

    /**
     * Supprime un tarif.
     */
    public function destroy(Tarif $tarif)
    {
        try {
            $tarif->delete();
            return $this->successResponse(null, "Tarif supprimé avec succès.");
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la suppression du tarif : " . $e->getMessage(), 500);
        }
    }
}
