<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LitigeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TarifController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\IsAdmin;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\DeliveryPersonController;


// Routes pour la gestion des commandes (CRUD + commandes récentes)
Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/recent', [OrderController::class, 'recent']);
    Route::get('/{order}', [OrderController::class, 'show']);
    Route::post('/', [OrderController::class, 'store']);
    Route::patch('/{order}/client-update', [OrderController::class, 'clientUpdate']);
    Route::patch('/{order}/admin-update', [OrderController::class, 'adminUpdate']);
    Route::delete('/{order}', [OrderController::class, 'destroy']);
});



// Route pour le dashboard (aperçu avec filtres)
Route::middleware('auth:sanctum', IsAdmin::class)->get('/dashboard/overview', [DashboardController::class, 'getOverviewData']);

// Routes pour la gestion des commissions
Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('commissions')->group(function () {
    Route::get('/', [CommissionController::class, 'index']);     // Liste des commissions
    Route::post('/', [CommissionController::class, 'store']);     // Créer une nouvelle commission
    Route::put('/{commission}', [CommissionController::class, 'update']); // Modifier une commission
});


// Routes pour la gestion des utilisateurs
Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{user}', [UserController::class, 'update']);
    Route::delete('/{user}', [UserController::class, 'destroy']);
    Route::patch('/{user}/toggle-block', [UserController::class, 'toggleBlock']);
});





// Routes pour la gestion des administrateurs
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/profile', [AdminController::class, 'profile']);
    Route::post('/admin/profile', [AdminController::class, 'updateProfile']);
});

Route::middleware('auth:sanctum')->prefix('litiges')->group(function () {
    // Liste des litiges (admin : tous / user : les siens)
    Route::get('/', [LitigeController::class, 'index']);

    // Création d'un litige par un utilisateur connecté
    Route::post('/', [LitigeController::class, 'store']);

    // Détail d’un litige + messages (avec model binding)
    Route::get('/{litige}', [LitigeController::class, 'show']);

    // Envoi de message dans un litige (admin ou user connecté)
    Route::post('/{litige}/messages', [LitigeController::class, 'sendMessage']);

    // Mise à jour d'un message par son auteur
    Route::put('/messages/{id}', [LitigeController::class, 'updateMessage']);

    // Suppression d'un message par son auteur
    Route::delete('/messages/{id}', [LitigeController::class, 'deleteMessage']);
});


// Routes pour l'historique  des rapports
Route::middleware(['auth:sanctum', IsAdmin::class])->get('/historique-rapports', [HistoriqueController::class, 'index']);

// Routes pour l'export des rapports
Route::middleware(['auth:sanctum', IsAdmin::class])
    ->get('/export-rapport', [ExportController::class, 'exporterRapport']);

// Routes pour la gestion des tarifs
Route::middleware(['auth:sanctum'])->prefix('tarifs')->group(function () {
    Route::get('/', [TarifController::class, 'index']);
    Route::get('/{tarif}', [TarifController::class, 'show']);

    // Admin uniquement
    Route::middleware(IsAdmin::class)->group(function () {
        Route::post('/', [TarifController::class, 'store']);
        Route::put('/{tarif}', [TarifController::class, 'update']);
        Route::delete('/{tarif}', [TarifController::class, 'destroy']);
    });
});


// 📥 Inscription
Route::post('/register/client', [AuthController::class, 'registerClient']);
Route::post('/register/restaurant', [AuthController::class, 'registerRestaurant']);
Route::post('/register/livreur', [AuthController::class, 'registerDeliveryPerson']);
Route::post('/register/livreur_entreprise', [AuthController::class, 'registerDeliveryEnterprise']);

// 🔑 Connexion
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    
    // 🔐 Infos utilisateur connecté
    Route::get('/me', [AuthController::class, 'me']);

    // 🔓 Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);

   
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);     // Voir profil utilisateur connecté
    Route::post('/profile', [ProfileController::class, 'update']);  // Modifier profil utilisateur connecté
});


Route::middleware(['auth:sanctum'])->group(function () {
    
    // 🔔 Lister les notifications de l'utilisateur connecté
    Route::get('/notifications', [NotificationController::class, 'index']);

    // ✅ Marquer une notification comme lue
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // ❌ Supprimer une notification
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});

// Groupe réservé aux administrateurs uniquement
Route::middleware(['auth:sanctum', IsAdmin::class])->group(function () {
    
    // 📨 Créer une notification pour un utilisateur
    Route::post('/notifications', [NotificationController::class, 'store']);
});




Route::middleware(['auth:sanctum'])->prefix('meals')->group(function () {
    Route::get('/', [MealController::class, 'index']);         // 🟢 Lister les repas
    Route::post('/', [MealController::class, 'store']);        // 🟢 Créer un repas
    Route::put('/{id}', [MealController::class, 'update']);    // 🟡 Modifier un repas
    Route::delete('/{id}', [MealController::class, 'destroy']); // 🔴 Supprimer un repas
});


Route::middleware('auth:sanctum')->prefix('livreurs')->group(function () {
    Route::post('/', [DeliveryPersonController::class, 'store']);       // Créer
    Route::get('/', [DeliveryPersonController::class, 'index']);        // Lister
    Route::get('/{livreur}', [DeliveryPersonController::class, 'show']); // Détail
    Route::delete('/{livreur}', [DeliveryPersonController::class, 'destroy']); // Supprimer
});
