<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\EmailLog;
use App\Models\Notification;
use App\Models\DeclarationDePerte;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function showAllEmails()
{
    // Récupérer tous les logs d'emails
    $emailLogs = EmailLog::all();

    return response()->json([
        'success' => true,
        'data' => $emailLogs->map(function($emailLog) {
            return [
                'id' => $emailLog->id,
                'from' => $emailLog->from,
                'to' => $emailLog->to,
                'subject' => $emailLog->subject,
                'body' => $emailLog->body,
                'created_at' => $emailLog->created_at,
            ];
        })
    ], 200);
}

public function showAllCorrespondenceEmails()
{
    // Récupérer tous les logs d'emails de correspondance
    $correspondenceEmails = EmailLog::where('subject', 'like', 'Correspondance%')->get();

    return response()->json([
        'success' => true,
        'data' => $correspondenceEmails->map(function($emailLog) {
            return [
                'id' => $emailLog->id,
                'from' => $emailLog->from,
                'to' => $emailLog->to,
                'subject' => $emailLog->subject,
                'body' => $emailLog->body,
                'created_at' => $emailLog->created_at,
            ];
        })
    ], 200);
}

public function showAllRestitutionEmails()
{
    // Récupérer tous les logs d'emails de demande de restitution
    $restitutionEmails = EmailLog::where('subject', 'like', 'Demande de restitution%')->get();

    return response()->json([
        'success' => true,
        'data' => $restitutionEmails->map(function($emailLog) {
            return [
                'id' => $emailLog->id,
                'from' => $emailLog->from,
                'to' => $emailLog->to,
                'subject' => $emailLog->subject,
                'body' => $emailLog->body,
                'created_at' => $emailLog->created_at,
            ];
        })
    ], 200);
}

// API Controller in Laravel (Example)
public function getAllData()
{
    $declarations = DeclarationDePerte::all();
    $publications = Document::all();
    // $restitutions = Restitution::all();
    $emailsSent = EmailLog::all();

    return response()->json([
        'declarations' => $declarations,
        'publications' => $publications,
        // 'restitutions' => $restitutions,
        'emailsSent' => $emailsSent,
    ]);
}


public function getRestitutionRequestCount()
{
    // Compte le nombre d'enregistrements dans la table des logs d'emails liés à une restitution
    $count = EmailLog::where('subject', 'LIKE', '%Demande de restitution du document%')->count();

    return response()->json(['count' => $count]);
}

}
