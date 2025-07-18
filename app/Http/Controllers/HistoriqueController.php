<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use App\Models\ReportLog;

class HistoriqueController extends ApiController
{
    public function index()
    {
        try {
            $logs = ReportLog::with('user')
                ->orderBy('generated_at', 'desc')
                ->get();

            return $this->successResponse($logs);  // méthode à définir dans ApiController pour répondre en JSON avec succès
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération de l'historique : " . $e->getMessage(), 500);
        }
    }
}
