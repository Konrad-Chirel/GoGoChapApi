<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RapportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ReportLog; // Assure-toi d’avoir ce modèle

class ExportController extends ApiController
{
    public function exporterRapport(Request $request)
    {
        try {
            $type = $request->query('type');       // financier, statistique, etc.
            $format = $request->query('format');   // pdf ou excel
            $dateDebut = $request->query('date_debut');
            $dateFin = $request->query('date_fin');

            // Validation minimale
            if (!$type || !$format || !$dateDebut || !$dateFin) {
                return $this->errorResponse("Champs requis manquants", 422);
            }

            $start = Carbon::parse($dateDebut)->startOfDay();
            $end = Carbon::parse($dateFin)->endOfDay();

            // Exemple de récupération de données (comme dans DashboardController)
            $data = DB::table('orders')
                ->select('id', 'order_date', 'total_price', 'delivery_fee', 'order_status')
                ->whereBetween('order_date', [$start, $end])
                ->where('order_status', 'completed')
                ->orderBy('order_date', 'asc')
                ->get();

            // Enregistrer l'historique du rapport généré
            ReportLog::create([
                'user_id' => auth()->id(),
                'type' => $type,
                'format' => $format,
                'date_debut' => $start->toDateString(),
                'date_fin' => $end->toDateString(),
                'generated_at' => now(),
            ]);

            // Export selon le format demandé
            if ($format === 'excel') {
                return Excel::download(new RapportExport($data), "rapport_{$type}.xlsx");
            } elseif ($format === 'pdf') {
                $pdf = Pdf::loadView('exports.rapport', [
                    'data' => $data,
                    'type' => $type,
                    'dateDebut' => $start->format('d/m/Y'),
                    'dateFin' => $end->format('d/m/Y'),
                ]);
                return $pdf->download("rapport_{$type}.pdf");
            }

            return $this->errorResponse("Format invalide", 400);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de l'export : " . $e->getMessage(), 500);
        }
    }
}
