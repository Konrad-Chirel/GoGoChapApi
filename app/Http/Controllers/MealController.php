<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MealController extends ApiController
{
    /**
     * Lister les repas du restaurant connecté
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'partenaire') {
                return $this->errorResponse('Accès non autorisé', 403);
            }

            $meals = Meal::where('restaurant_id', $user->restaurant_id)->latest()->get();

            return $this->successResponse($meals);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur : " . $e->getMessage(), 500);
        }
    }

    /**
     * Créer un nouveau repas
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'partenaire') {
                return $this->errorResponse('Accès non autorisé', 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|image|max:2048',
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('meals', 'public');
            }

            $meal = Meal::create([
                'restaurant_id' => $user->restaurant_id,
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'image' => $imagePath,
            ]);

            return $this->successResponse($meal, "Repas créé avec succès");
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la création : " . $e->getMessage(), 500);
        }
    }

    /**
     * Modifier un repas existant
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'partenaire') {
                return $this->errorResponse('Accès non autorisé', 403);
            }

            $meal = Meal::where('id', $id)
                ->where('restaurant_id', $user->restaurant_id)
                ->first();

            if (!$meal) {
                return $this->errorResponse("Repas non trouvé", 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:100',
                'description' => 'sometimes|string',
                'price' => 'sometimes|numeric|min:0',
                'available' => 'sometimes|boolean',
                'image' => 'nullable|image|max:2048',
            ]);

            // Gérer le remplacement de l’image
            if ($request->hasFile('image')) {
                if ($meal->image) {
                    Storage::disk('public')->delete($meal->image);
                }
                $meal->image = $request->file('image')->store('meals', 'public');
            }

            $meal->update($validated);

            return $this->successResponse($meal, "Repas mis à jour");
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la mise à jour : " . $e->getMessage(), 500);
        }
    }

    /**
     * Supprimer un repas
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'partenaire') {
                return $this->errorResponse('Accès non autorisé', 403);
            }

            $meal = Meal::where('id', $id)
                ->where('restaurant_id', $user->restaurant_id)
                ->first();

            if (!$meal) {
                return $this->errorResponse("Repas non trouvé", 404);
            }

            if ($meal->image) {
                Storage::disk('public')->delete($meal->image);
            }

            $meal->delete();

            return $this->successResponse(null, "Repas supprimé avec succès");
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la suppression : " . $e->getMessage(), 500);
        }
    }

    /**
     * Afficher un seul repas
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'partenaire') {
                return $this->errorResponse('Accès non autorisé', 403);
            }

            $meal = Meal::where('id', $id)
                ->where('restaurant_id', $user->restaurant_id)
                ->first();

            if (!$meal) {
                return $this->errorResponse("Repas non trouvé", 404);
            }

            return $this->successResponse($meal);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération : " . $e->getMessage(), 500);
        }
    }
}
