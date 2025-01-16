<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\DeclarationDePerteController;
use App\Http\Controllers\NotificationController;
use Illuminate\Notifications\Notification;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
Route::get('/users', [AuthController::class, 'getAllUsersWithRoles'])->middleware('auth:api');
Route::put('change-password', [AuthController::class, 'changePassword'])->middleware('auth:api');
Route::delete('users/{id}', [AuthController::class, 'deleteUser'])->middleware('auth:api');
Route::post('create-admin', [AuthController::class, 'createAdmin'])->middleware('auth:api');
Route::put('profil', [AuthController::class, 'updateProfile'])->middleware('auth:api');
Route::get('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);


Route::middleware('auth:api')->group(function () {
    Route::apiResource('document-types', DocumentTypeController::class);
    Route::apiResource('documents', DocumentController::class)->only('store','show','index','destroy');
    Route::apiResource('declarations', DeclarationDePerteController::class);
    Route::get('trash', [DeclarationDePerteController::class, 'trashedDeclarations']);
    Route::post('/declarations/restore/{id}', [DeclarationDePerteController::class, 'restoreTrashedDeclaration']);
    Route::get('trashed', [DocumentController::class, 'trashedDocuments']);
    Route::post('/documents/restore/{id}', [DocumentController::class, 'restoreTrashedDocument']);
    Route::apiResource('comments', CommentaireController::class);
    Route::post('documents/{id}/restitution', [DocumentController::class, 'requestRestitution']);
    route::get('mypub',[DocumentController::class, 'OwnPub']);
    Route::get('mydec', [DeclarationDePerteController::class, 'getUserDeclarations']);
    Route::get('/correspondence', [NotificationController::class, 'showAllCorrespondenceEmails']);
    Route::get('/restitution', [NotificationController::class, 'showAllRestitutionEmails']);
    Route::get('/emails', [NotificationController::class,'showAllEmails']);
    Route::get('/notification', [NotificationController::class, 'getAllData']);
    Route::get('/notifications', [NotificationController::class, 'getNewNotifications']);
    Route::get('/restitution-count', [NotificationController::class, 'getRestitutionRequestCount']);
    Route::get('/notifications', [NotificationController::class, 'index']); // Pour récupérer toutes les notifications
    Route::patch('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::get('all', [DocumentController::class, 'getAllPublications']);
    Route::get('doctype', [DocumentController::class, 'getPublicationsByType']);
    Route::get('taux', [DocumentController::class, 'getRestitutionData']);
    Route::get('mail', [DocumentController::class, 'getEmailActivity']);
    Route::get('stat', [DocumentController::class, 'getStatistics']);
    Route::get('status-count', [DocumentController::class,'getDocumentStatusCountWithTrashed']);
    Route::get('supp', [DocumentController::class,'getDeletedDocuments']);
    Route::get('recup', [DocumentController::class,'getRecoveredDocuments']);
    Route::get('nonrecup', [DocumentController::class,'getNotRecoveredDocuments']);


});
Route::apiResource('document', DocumentController::class);
Route::get('documents/{document_id}/comments', [CommentaireController::class, 'getCommentairesByDocument']);
Route::get('documents/{id}', [DocumentController::class, 'show']);
Route::put('document/{id}', [DocumentController::class,'update'] );
Route::get('lieu', [DocumentController::class,'getPublicationsByLocation']);





