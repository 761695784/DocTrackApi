<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificatDePerteController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DeclarationDePerteController;
use App\Http\Controllers\EmailVerificationController;

// Routes publiques (aucune authentification requise)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('/auth/google/callback', [AuthController::class, 'handleGoogleLogin']);
Route::post('/auth/google/finalize-account-creation', [AuthController::class, 'finalizeAccountCreation']);
Route::post('/found-qr', [QrCodeController::class, 'handleFoundQr']);

Route::post('/verify-email', [EmailVerificationController::class, 'verify']);


// Routes publiques pour les documents (lecture seule)
Route::get('documents', [DocumentController::class, 'index']);
Route::get('documents/{slug}', [DocumentController::class, 'show']);
Route::get('documents/{document_slug}/comments', [CommentaireController::class, 'getCommentairesByDocument']);
Route::get('lieu', [DocumentController::class, 'getPublicationsByLocation']);
Route::apiResource('document', DocumentController::class);

// Routes protégées par authentification
Route::middleware('auth:api')->group(function () {
    // Routes utilisateur
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::put('change-password', [AuthController::class, 'changePassword']);
    Route::put('profil', [AuthController::class, 'updateProfile']);
    Route::post('/renew-qr-code', [AuthController::class, 'renewQrCode']);
    Route::get('/my-certificats', [CertificatDePerteController::class, 'mesCertificats']);


    // Routes admin
    Route::get('/users', [AuthController::class, 'getAllUsersWithRoles']);
    Route::delete('users/{slug}', [AuthController::class, 'deleteUser']);
    Route::post('create-admin', [AuthController::class, 'createAdmin']);
    Route::get('/activity-logs', [AuthController::class, 'getActivityLogs']);
    Route::get('/activity-logs/{subject_type}', [AuthController::class, 'getLogsByType']);
    Route::get('/admin/certificats', [CertificatDePerteController::class, 'index']);
    Route::get('/certificats/{slug}/voir', [CertificatDePerteController::class, 'voir']);
    Route::get('/certificats/{slug}/telecharger', [CertificatDePerteController::class, 'telecharger']);


    // Routes pour les documents
    Route::post('documents', [DocumentController::class, 'store']);
    Route::put('documents/{slug}', [DocumentController::class, 'update']);
    Route::delete('documents/{slug}', [DocumentController::class, 'destroy']);
    Route::post('documents/restore/{slug}', [DocumentController::class, 'restoreTrashedDocument']);
    Route::get('trashed', [DocumentController::class, 'trashedDocuments']);
    Route::post('documents/{slug}/restitution', [DocumentController::class, 'requestRestitution']);
    Route::get('my-publications', [DocumentController::class, 'OwnPub']);

    // Routes pour les déclarations de perte
    // Route::apiResource('declarations', DeclarationDePerteController::class);
    Route::post('declarations', [DeclarationDePerteController::class,'store']);
    Route::get('declarations', [DeclarationDePerteController::class, 'index']);
    Route::get('declarations/{slug}', [DeclarationDePerteController::class, 'show']);
    Route::delete('declarations/{slug}', [DeclarationDePerteController::class, 'destroy']);
    Route::get('trashed-declarations', [DeclarationDePerteController::class, 'trashedDeclarations']);
    Route::post('declarations/restore/{slug}', [DeclarationDePerteController::class, 'restoreTrashedDeclaration']);
    Route::get('my-declarations', [DeclarationDePerteController::class, 'getUserDeclarations']);

    // Routes pour les commentaires
    Route::apiResource('comments', CommentaireController::class);

    // Routes pour les notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/{slug}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::get('correspondence-emails', [NotificationController::class, 'showAllCorrespondenceEmails']);
    Route::get('restitution-emails', [NotificationController::class, 'showAllRestitutionEmails']);
    Route::get('all-emails', [NotificationController::class, 'showAllEmails']);
    Route::get('all-notifications', [NotificationController::class, 'getAllData']);
    Route::get('new-notifications', [NotificationController::class, 'getNewNotifications']);
    Route::get('restitution-count', [NotificationController::class, 'getRestitutionRequestCount']);

    // Autres routes protégées for admin
    Route::get('all-publications', [DocumentController::class, 'getAllPublications']);
    Route::get('publications-by-type', [DocumentController::class, 'getPublicationsByType']);
    Route::get('restitution-data', [DocumentController::class, 'getRestitutionData']);
    Route::get('email-activity', [DocumentController::class, 'getEmailActivity']);
    Route::get('statistics', [DocumentController::class, 'getStatistics']);
    Route::get('status-count', [DocumentController::class, 'getDocumentStatusCountWithTrashed']);
    Route::get('deleted-documents', [DocumentController::class, 'getDeletedDocuments']);
    Route::get('recovered-documents', [DocumentController::class, 'getRecoveredDocuments']);
    Route::get('not-recovered-documents', [DocumentController::class, 'getNotRecoveredDocuments']);
});

// Routes pour les types de documents (protégées)
Route::apiResource('document-types', DocumentTypeController::class)->middleware('auth:api');

