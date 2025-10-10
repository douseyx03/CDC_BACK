<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\BackofficeDemandeController;

//------------------------Authentification/Connexion--------------------------------------------------------------------------
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

    //-------------------Demandes----------------------------------------------------------------------------------------------
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

    //------------------Documents-------------------------------------------------------------------------------
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

// Liste tous les services disponibles côté administration.
Route::get('/services', [ServiceController::class, 'index'])->middleware('auth:sanctum');
Route::get('/services/{service}', [ServiceController::class, 'show'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'role:super-admin'])->prefix('backoffice')->group(function () {

    //------------------Services--------------------------------------------------------------------------
    // Crée un nouveau service disponible pour les utilisateurs.
    Route::post('/services', [ServiceController::class, 'store']);


    // Met à jour complètement un service existant.
    Route::put('/services/{service}', [ServiceController::class, 'update']);

    // Met à jour partiellement un service existant.
    Route::patch('/services/{service}', [ServiceController::class, 'update']);

    // Supprime définitivement un service.
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);

    //-----------------------------------Demandes & Documents-----------------------------------
    // Liste toutes les demandes avec leurs métadonnées.
    Route::get('/demandes', [BackofficeDemandeController::class, 'index']);

    // Affiche le détail complet d'une demande spécifique.
    Route::get('/demandes/{demande}', [BackofficeDemandeController::class, 'show']);

    // Met à jour une demande (statut, urgence, description, pièces jointes).
    Route::post('/demandes/{demande}', [BackofficeDemandeController::class, 'update']);

    // Ajoute de nouveaux documents à une demande.
    Route::post('/demandes/{demande}/documents', [BackofficeDemandeController::class, 'storeDocument']);

    // Met à jour les métadonnées d'un document.
    Route::patch('/documents/{document}', [BackofficeDemandeController::class, 'updateDocument']);

    // Supprime un document lié à une demande.
    Route::delete('/documents/{document}', [BackofficeDemandeController::class, 'destroyDocument']);

    //-----------------------------------Agents---------------------------------------------------
    // Liste tous les agents du back-office.
    Route::get('/agents', [AgentController::class, 'index']);

    // Crée un nouvel agent et lui assigne ses rôles.
    Route::post('/agents', [AgentController::class, 'store']);

    // Affiche le détail d'un agent spécifique.
    Route::get('/agents/{agent}', [AgentController::class, 'show']);

    // Met à jour complètement les informations d'un agent.
    Route::put('/agents/{agent}', [AgentController::class, 'update']);

    // Met à jour partiellement les informations d'un agent.
    Route::patch('/agents/{agent}', [AgentController::class, 'update']);

    // Supprime (soft delete) un agent et retire ses rôles.
    Route::delete('/agents/{agent}', [AgentController::class, 'destroy']);

    //--------------------------------Roles-----------------------------------------------------
    // Liste tous les rôles disponibles.
    Route::get('/roles', [RoleController::class, 'index']);

    // Crée un nouveau rôle et lui associe des permissions.
    Route::post('/roles', [RoleController::class, 'store']);

    // Affiche le détail d'un rôle spécifique.
    Route::get('/roles/{role}', [RoleController::class, 'show']);

    // Met à jour un rôle et ses permissions associées.
    Route::put('/roles/{role}', [RoleController::class, 'update']);

    // Met à jour partiellement un rôle et ses permissions.
    Route::patch('/roles/{role}', [RoleController::class, 'update']);

    // Supprime un rôle.
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);

    //-----------------------Permission------------------------------------------------------------------
    // Liste toutes les permissions disponibles.
    Route::get('/permissions', [PermissionController::class, 'index']);

    // Crée une nouvelle permission.
    Route::post('/permissions', [PermissionController::class, 'store']);

    // Affiche le détail d'une permission spécifique.
    Route::get('/permissions/{permission}', [PermissionController::class, 'show']);

    // Met à jour une permission existante.
    Route::put('/permissions/{permission}', [PermissionController::class, 'update']);

    // Met à jour partiellement une permission existante.
    Route::patch('/permissions/{permission}', [PermissionController::class, 'update']);

    // Supprime une permission.
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy']);
});
