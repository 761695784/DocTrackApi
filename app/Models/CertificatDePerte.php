<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificatDePerte extends Model
{
   protected $guarded = [];

        public function declarationDePerte() // ← camelCase cohérent
        {
            return $this->belongsTo(DeclarationDePerte::class, 'declaration_de_perte_id');
        }
        
        public function documentType()
        {
            return $this->belongsTo(DocumentType::class, 'document_type_id');
        }


}
