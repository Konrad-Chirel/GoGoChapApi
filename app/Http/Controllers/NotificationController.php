<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends ApiController
{
    /** 
     * Lister les notifications de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $notifications = Notification::where('user_id', $user->id)
                ->where('user_type', $user->role)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($notifications);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur de récupération des notifications : " . $e->getMessage(), 500);
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($id, Request $request)
    {
        try {
            $user = $request->user();

            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->where('user_type', $user->role)
                ->first();

            if (!$notification) {
                return $this->errorResponse("Notification non trouvée", 404);
            }

            $notification->read = true;
            $notification->save();

            return $this->successResponse($notification, "Notification marquée comme lue");
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur : " . $e->getMessage(), 500);
        }
    }

    /**
     * Créer une nouvelle notification (admin ou système uniquement)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'   => 'required|integer|exists:users,id',
            'user_type' => 'required|in:client,livreur,entreprise,admin',
            'type'      => 'required|string|max:50',
            'message'   => 'required|string',
            'order_id'  => 'nullable|exists:orders,id',
        ]);

        try {
            $notification = Notification::create([
                'user_id'   => $validated['user_id'],
                'user_type' => $validated['user_type'],
                'type'      => $validated['type'],
                'message'   => $validated['message'],
                'order_id'  => $validated['order_id'] ?? null,
                'read'      => false,
            ]);

            return $this->successResponse($notification, "Notification envoyée");
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de l'envoi : " . $e->getMessage(), 500);
        }
    }

    public function destroy($id, Request $request)
{
    try {
        $user = $request->user();

        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->where('user_type', $user->role)
            ->first();

        if (!$notification) {
            return $this->errorResponse("Notification non trouvée", 404);
        }

        $notification->delete();

        return $this->successResponse(null, "Notification supprimée avec succès");
    } catch (\Exception $e) {
        return $this->errorResponse("Erreur lors de la suppression : " . $e->getMessage(), 500);
    }
}

}
