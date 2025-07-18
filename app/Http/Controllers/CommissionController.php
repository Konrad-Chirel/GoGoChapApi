<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\Http\Request;

class CommissionController extends ApiController
{
    /**
     * Récupérer la configuration actuelle des commissions.
     */
    public function index()
    {
        $commission = Commission::latest()->first();

        return $this->successResponse($commission, 'Dernière configuration des commissions.');
    }

    /**
     * Créer une nouvelle configuration de commissions.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_percentage' => 'required|numeric|min:0|max:100',
            'delivery_percentage' => 'required|numeric|min:0|max:100',
            'min_delivery_fee' => 'required|numeric|min:0',
        ]);

        try {
            $commission = Commission::create($validated);
            return $this->successResponse($commission, 'Commission enregistrée avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de l'enregistrement : " . $e->getMessage(), 500);
        }
    }

    /**
     * Modifier une configuration existante de commissions.
     */
    public function update(Request $request, Commission $commission)
    {
        $validated = $request->validate([
            'product_percentage' => 'required|numeric|min:0|max:100',
            'delivery_percentage' => 'required|numeric|min:0|max:100',
            'min_delivery_fee' => 'required|numeric|min:0',
        ]);

        try {
            $commission->update($validated);
            return $this->successResponse($commission, 'Commission mise à jour avec succès.');
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la mise à jour : " . $e->getMessage(), 500);
        }
    }
}
