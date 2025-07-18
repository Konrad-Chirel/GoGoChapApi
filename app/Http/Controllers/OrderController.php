<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Tarif;
use Carbon\Carbon;
use Exception;
use App\Events\OrderEvent;

class OrderController extends ApiController
{
    public function index()
    {
        try {
            $orders = Order::with(['orderItems.meal', 'customer.user'])
                ->orderBy('order_date', 'desc')
                ->paginate(20);

            return $this->successResponse($orders, "Liste des commandes");
        } catch (Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération des commandes : " . $e->getMessage(), 500);
        }
    }

    public function recent()
    {
        try {
            $orders = Order::with(['orderItems.meal', 'customer.user'])
                ->orderBy('order_date', 'desc')
                ->limit(10)
                ->get();

            return $this->successResponse($orders, "10 commandes récentes");
        } catch (Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération des commandes récentes : " . $e->getMessage(), 500);
        }
    }

    public function show(Order $order)
    {
        try {
            $order->load(['orderItems.meal', 'customer.user']);
            return $this->successResponse($order, "Commande trouvée");
        } catch (Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération de la commande : " . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'restaurant_id'   => 'required|exists:restaurants,id',
            'order_items'     => 'required|array|min:1',
            'distance'        => 'required|numeric|min:0',
            'intemperies'     => 'required|boolean',
            'heure_commande'  => 'required|date_format:H:i',
        ]);

        try {
            $tarifs = Tarif::all()->keyBy('titre');
            $fraisLivraison = 0;

            if ($validated['distance'] <= 3 && isset($tarifs['Frais de livraison standard'])) {
                $fraisLivraison += $tarifs['Frais de livraison standard']->montant;
            }

            if ($validated['distance'] > 3 && isset($tarifs['Frais par km supplémentaire'])) {
                $kmSupp = $validated['distance'] - 3;
                $fraisLivraison += $kmSupp * $tarifs['Frais par km supplémentaire']->montant;
            }

            if ($validated['intemperies'] && isset($tarifs['Bonus intempéries'])) {
                $fraisLivraison += $tarifs['Bonus intempéries']->montant;
            }

            $heure = Carbon::createFromFormat('H:i', $validated['heure_commande'])->format('H');
            if ($heure >= 19 && $heure <= 21 && isset($tarifs['Bonus heures de pointe'])) {
                $fraisLivraison += $tarifs['Bonus heures de pointe']->montant;
            }

            $order = new Order();
            $order->customer_id   = $validated['customer_id'];
            $order->restaurant_id = $validated['restaurant_id'];
            $order->order_status  = 'pending';
            $order->delivery_fee  = $fraisLivraison;
            $order->total_price   = 0;
            $order->save();

            $totalPrice = 0;

            foreach ($validated['order_items'] as $item) {
                $order->orderItems()->create([
                    'meal_id'  => $item['meal_id'],
                    'quantity' => $item['quantity'],
                    'price'    => $item['price'],
                ]);
                $totalPrice += $item['price'] * $item['quantity'];
            }

            $order->total_price = $totalPrice + $fraisLivraison;
            $order->save();

            $order->load(['orderItems.meal', 'customer.user']);

            event(new OrderEvent($order, 'created'));

            return $this->successResponse($order, "Commande créée avec succès", 201);
        } catch (Exception $e) {
            return $this->errorResponse("Erreur lors de la création de la commande : " . $e->getMessage(), 500);
        }
    }

    public function clientUpdate(Request $request, Order $order)
    {
        if (auth()->user()->customer_id !== $order->customer_id) {
            return $this->errorResponse("Vous n'avez pas le droit de modifier cette commande", 403);
        }

        if (!in_array($order->order_status, ['pending', 'confirmed'])) {
            return $this->errorResponse("La commande ne peut plus être modifiée", 403);
        }

        $validated = $request->validate([
            'delivery_address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'order_items' => 'nullable|array|min:1',
            'order_items.*.meal_id' => 'required_with:order_items|exists:meals,id',
            'order_items.*.quantity' => 'required_with:order_items|integer|min:1',
            'order_items.*.price' => 'required_with:order_items|numeric|min:0',
        ]);

        try {
            \DB::beginTransaction();

            $order->update([
                'delivery_address' => $validated['delivery_address'] ?? $order->delivery_address,
                'notes' => $validated['notes'] ?? $order->notes,
            ]);

            $totalPrice = 0;

            if (isset($validated['order_items'])) {
                $order->orderItems()->delete();

                foreach ($validated['order_items'] as $item) {
                    $order->orderItems()->create([
                        'meal_id' => $item['meal_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                    $totalPrice += $item['price'] * $item['quantity'];
                }

                $order->total_price = $totalPrice + $order->delivery_fee;
                $order->save();

                $order->load(['orderItems.meal', 'customer.user']);

                event(new OrderEvent($order, 'updated_by_client'));
            }

            \DB::commit();
            return $this->successResponse($order, "Commande modifiée par le client");
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->errorResponse("Erreur lors de la modification : " . $e->getMessage(), 500);
        }
    }

    public function adminUpdate(Request $request, Order $order)
    {
        if (auth()->user()->role !== 'admin') {
            return $this->errorResponse("Non autorisé", 403);
        }

        $validated = $request->validate([
            'order_status'       => 'sometimes|in:pending,confirmed,in_progress,completed,cancelled',
            'delivery_person_id' => 'sometimes|exists:delivery_people,id',
            'delivery_date'      => 'sometimes|date',
            'status'             => 'sometimes|string',
        ]);

        $order->update($validated);

        $order->load(['orderItems.meal', 'customer.user']);

        event(new OrderEvent($order, 'status_updated'));

        return $this->successResponse($order, "Statut de la commande mis à jour");
    }

    public function destroy(Order $order)
    {
        try {
            $order->delete();
            return $this->successResponse(null, "Commande supprimée");
        } catch (Exception $e) {
            return $this->errorResponse("Erreur lors de la suppression de la commande : " . $e->getMessage(), 500);
        }
    }
}
