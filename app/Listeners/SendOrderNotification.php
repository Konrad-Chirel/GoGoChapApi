<?php
namespace App\Listeners;

use App\Events\OrderEvent;
use App\Models\Notification;

class SendOrderNotification
{
    public function handle(OrderEvent $event)
    {
        $order = $event->order;
        $action = $event->action;

        $message = match ($action) {
            'created' => "Votre commande #{$order->id} a été créée.",
            'updated_by_client' => "Votre commande #{$order->id} a été modifiée.",
            'status_updated' => "Le statut de la commande #{$order->id} est maintenant : {$order->order_status}.",
            default => "Une action a eu lieu sur la commande #{$order->id}.",
        };

        // Fonction pour créer notification si elle n'existe pas déjà
        $createNotification = function ($userId, $userType) use ($message, $order) {
            $exists = Notification::where('user_id', $userId)
                ->where('type', 'commande')
                ->where('order_id', $order->id)
                ->where('message', $message)
                ->exists();

            if (!$exists) {
                Notification::create([
                    'user_id' => $userId,
                    'user_type' => $userType,
                    'type' => 'commande',
                    'message' => $message,
                    'order_id' => $order->id,
                ]);
            }
        };

        // Notification client
        if ($order->customer && $order->customer->user) {
            $createNotification($order->customer->user->id, 'client');
        }

        // Notification livreur (si assigné)
        if ($order->deliveryPerson && $order->deliveryPerson->user) {
            $createNotification($order->deliveryPerson->user->id, 'livreur');
        }

        // Notification admin(s) — on récupère distinct pour éviter doublons
        $admins = \App\Models\User::where('role', 'admin')->distinct('id')->get();
        foreach ($admins as $admin) {
            $createNotification($admin->id, 'admin');
        }
    }
}
