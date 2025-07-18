<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;  // hérite de ApiController
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends ApiController
{
    public function getOverviewData(Request $request)
    {
        $filter = $request->query('filter', 'month'); // 'week_current', 'week_previous', 'month'
        $now = Carbon::now();

        // Dates limites en fonction du filtre
        if ($filter === 'week_previous') {
            $start = $now->copy()->startOfWeek()->subWeek();
            $end = $now->copy()->startOfWeek()->subSecond();
            $groupFormat = 'Y-m-d'; // groupement par jour
            $periodLabel = 'day';
        } elseif ($filter === 'week_current') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
            $groupFormat = 'Y-m-d'; // groupement par jour
            $periodLabel = 'day';
        } else { // par défaut mois en cours
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
            $groupFormat = 'Y-\WW'; // groupement par semaine (année + semaine ISO)
            $periodLabel = 'week';
        }

        // Récupérer la commission actuelle (on prend la dernière entrée)
        $commission = DB::table('commissions')->latest()->first();
        $productCommission = $commission->product_percentage ?? 20;
        $deliveryCommission = $commission->delivery_percentage ?? 20;
        $minDeliveryFee = $commission->min_delivery_fee ?? 500;

        // Requête pour agrégation des données par période
        $data = DB::table('orders')
            ->selectRaw("
                to_char(order_date, ?) as periode,
                SUM(total_price) as revenu,
                SUM(delivery_fee) as depense
            ", [$groupFormat])
            ->whereBetween('order_date', [$start, $end])
            ->where('order_status', 'completed')
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();

        // Récupérer les ventes (quantité) par même période
        $salesData = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw("to_char(orders.order_date, ?) as periode, SUM(order_items.quantity) as ventes", [$groupFormat])
            ->whereBetween('orders.order_date', [$start, $end])
            ->where('orders.order_status', 'completed')
            ->groupBy('periode')
            ->orderBy('periode')
            ->get()
            ->keyBy('periode'); // pour accès rapide

        // Préparer la réponse finale avec commissions appliquées
        $result = [];

        foreach ($data as $item) {
            $ventes = $salesData[$item->periode]->ventes ?? 0;

            $gochapRevenue = $item->revenu * ($productCommission / 100);
            $partnerRevenue = $item->revenu - $gochapRevenue;

            $gochapDeliveryRevenue = $item->depense * ($deliveryCommission / 100);
            $partnerDeliveryRevenue = $item->depense - $gochapDeliveryRevenue;

            $result[] = [
                $periodLabel => $item->periode,
                'revenu' => round($item->revenu, 2),
                'gochap_revenue' => round($gochapRevenue, 2),
                'partner_revenue' => round($partnerRevenue, 2),
                'depense' => round($item->depense, 2),
                'gochap_delivery_revenue' => round($gochapDeliveryRevenue, 2),
                'partner_delivery_revenue' => round($partnerDeliveryRevenue, 2),
                'ventes' => (int) $ventes,
            ];
        }

        return $this->successResponse([
            'data' => $result,
            'commission' => [
                'product_percentage' => $productCommission,
                'delivery_percentage' => $deliveryCommission,
                'min_delivery_fee' => $minDeliveryFee,
            ],
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'filter' => $filter,
        ], "Données d'aperçu pour la période sélectionnée");
    }
}
