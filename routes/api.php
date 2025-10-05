<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DocumentController;

Route::prefix('auth')->group(function () {
    // Inscription d'un utilisateur avec vérification email/téléphone.
    Route::post('/register', [AuthController::class, 'register']);
    // Tentative de connexion en déclenchant l'OTP si nécessaire.
   Route::post('/login', [AuthController::class, 'login']);
    // Traite le lien magique de confirmation d'adresse e-mail.
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Endpoint de validation d'OTP sans être connecté.
    Route::post('/phone/verify', [AuthController::class, 'verifyPhone'])
        ->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function () {
        // Déconnexion de l'utilisateur courant.
        Route::post('/logout', [AuthController::class, 'logout']);
        // Génère un nouvel OTP pour le téléphone non vérifié.
        Route::post('/phone/otp', [AuthController::class, 'requestPhoneOtp']);
        // Retourne le profil complet de l'utilisateur connecté.
        Route::get('/me', function (Request $request) {
            return $request->user();
        });

    });
});


Route::middleware('auth:sanctum')->group(function () {
    // Liste toutes les demandes accessibles à l'utilisateur connecté ou à l'admin.
    Route::get('/demandes', [DemandeController::class, 'index']);

    // Crée une nouvelle demande pour le profil authentifié.
    Route::post('/demandes', [DemandeController::class, 'store']);

    // Affiche le détail d'une demande spécifique.
    Route::get('/demandes/{demande}', [DemandeController::class, 'show']);

    // Met à jour complètement une demande existante.
    Route::put('/demandes/{demande}', [DemandeController::class, 'update']);

    // Met à jour partiellement une demande existante.
    Route::patch('/demandes/{demande}', [DemandeController::class, 'update']);

    // Supprime définitivement une demande.
    Route::delete('/demandes/{demande}', [DemandeController::class, 'destroy']);

    // Liste les documents associés à une demande donnée.
    Route::get('/demandes/{demande}/documents', [DocumentController::class, 'index']);

    // Ajoute un document à une demande spécifique.
    Route::post('/demandes/{demande}/documents', [DocumentController::class, 'store']);

    // Affiche le détail d'un document particulier.
    Route::get('/documents/{document}', [DocumentController::class, 'show']);

    // Met à jour complètement un document existant.
    Route::put('/documents/{document}', [DocumentController::class, 'update']);

    // Met à jour partiellement un document existant.
    Route::patch('/documents/{document}', [DocumentController::class, 'update']);

    // Supprime définitivement un document.
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // Liste tous les services disponibles côté administration.
    Route::get('/services', [ServiceController::class, 'index']);

    // Crée un nouveau service disponible pour les utilisateurs.
    Route::post('/services', [ServiceController::class, 'store']);

    // Affiche le détail d'un service pour consultation.
    Route::get('/services/{service}', [ServiceController::class, 'show']);

    // Met à jour complètement un service existant.
    Route::put('/services/{service}', [ServiceController::class, 'update']);

    // Met à jour partiellement un service existant.
    Route::patch('/services/{service}', [ServiceController::class, 'update']);

    // Supprime définitivement un service.
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
});
