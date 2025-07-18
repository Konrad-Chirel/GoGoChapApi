<?php

namespace App\Http\Controllers;

use App\Models\Litige;
use App\Models\Message;
use Illuminate\Http\Request;

class LitigeController extends ApiController
{
    public function index(Request $request)
    {
        try {
            $user = $request->user(); // récupère depuis le token sanctum
    
            $query = Litige::with('user');
    
            // Si ce n'est pas un admin, on limite aux litiges de l'utilisateur connecté
            if ($user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }
    
            // Si un filtre "status" est passé (ouvert, fermé)
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
    
            $litiges = $query->orderBy('updated_at', 'desc')->get();
    
            return $this->successResponse($litiges);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération des litiges : " . $e->getMessage(), 500);
        }
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:urgent,moyen,faible',
            'priority' => 'required|in:ouvert,fermé',
        ]);
    
        try {
            $user = $request->user();
    
            if (!$user) {
                return $this->errorResponse('Utilisateur non authentifié', 401);
            }
    
            if ($user->role === 'admin') {
                return $this->errorResponse('Un administrateur ne peut pas créer de litige', 403);
            }
    
            $litige = Litige::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'priority' => $request->priority,
            ]);
    
            return $this->successResponse($litige, 'Litige créé avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la création du litige : ' . $e->getMessage(), 500);
        }
    }
    

    

    // ✅ Voir un litige + tous ses messages (admin + user)
    public function show(Litige $litige)
    {
        try {
            // Chargement des relations nécessaires
            $litige->load(['user', 'messages.admin', 'messages.user']);
    
            return $this->successResponse($litige);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors du chargement du litige : " . $e->getMessage(), 500);
        }
    }
    
    

    // ✅ Envoi de message (par un admin ou un user connecté)
    public function sendMessage(Request $request, $litigeId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);
    
        try {
            $litige = Litige::find($litigeId);
            if (!$litige) {
                return $this->errorResponse('Litige non trouvé', 404);
            }
    
            $user = $request->user();
    
            if (!$user) {
                return $this->errorResponse('Utilisateur non authentifié', 401);
            }
    
            $messageData = [
                'litige_id' => $litige->id,
                'content' => $request->content,
                'sent_at' => now(),
            ];
    
            if ($user->role === 'admin') {
                $messageData['admin_id'] = $user->admin_id ?? $user->id;
            } else {
                $messageData['user_id'] = $user->id;
            }
    
            $message = Message::create($messageData);
    
            return $this->successResponse($message, 'Message envoyé avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de l'envoi du message : " . $e->getMessage(), 500);
        }
    }

    
    public function updateMessage(Request $request, $messageId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);
    
        try {
            $user = $request->user();
    
            $message = Message::find($messageId);
            if (!$message) {
                return $this->errorResponse('Message non trouvé', 404);
            }
    
            // Vérifier si c'est bien l'auteur du message (admin ou user)
            $isAuthor = false;
    
            if ($user->role === 'admin' && $message->admin_id === $user->admin_id) {
                $isAuthor = true;
            }
    
            if ($user->role !== 'admin' && $message->user_id === $user->id) {
                $isAuthor = true;
            }
    
            if (!$isAuthor) {
                return $this->errorResponse('Accès non autorisé pour modifier ce message', 403);
            }
    
            $message->content = $request->content;
            $message->save();
    
            return $this->successResponse($message, 'Message mis à jour avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la mise à jour du message : ' . $e->getMessage(), 500);
        }
    }
    

    public function deleteMessage(Request $request, $messageId)
    {
        try {
            $user = $request->user();
    
            $message = Message::find($messageId);
            if (!$message) {
                return $this->errorResponse('Message non trouvé', 404);
            }
    
            $isAuthor = false;
    
            if ($user->role === 'admin' && $message->admin_id === $user->admin_id) {
                $isAuthor = true;
            }
    
            if ($user->role !== 'admin' && $message->user_id === $user->id) {
                $isAuthor = true;
            }
    
            if (!$isAuthor) {
                return $this->errorResponse('Accès non autorisé pour supprimer ce message', 403);
            }
    
            $message->delete();
    
            return $this->successResponse(null, 'Message supprimé avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la suppression du message : ' . $e->getMessage(), 500);
        }
    }
    
    
}





