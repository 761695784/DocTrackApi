<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\DeclarationDePerteController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
Route::get('/users', [AuthController::class, 'getAllUsersWithRoles'])->middleware('auth:api');
Route::put('change-password', [AuthController::class, 'changePassword'])->middleware('auth:api');
Route::delete('users/{id}', [AuthController::class, 'deleteUser'])->middleware('auth:api');
Route::post('create-admin', [AuthController::class, 'createAdmin'])->middleware('auth:api');


Route::middleware('auth:api')->group(function () {
    Route::apiResource('document-types', DocumentTypeController::class);
    Route::apiResource('documents', DocumentController::class);
    Route::apiResource('declarations', DeclarationDePerteController::class);
    Route::apiResource('comments', CommentaireController::class);

});
// Route::apiResource('document', DocumentController::class);


