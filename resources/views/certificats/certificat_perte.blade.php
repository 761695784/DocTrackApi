{{-- <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat de Perte</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            line-height: 1.5;
            color: #000;
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            text-decoration: underline;
        }

        .section {
            margin-bottom: 25px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
        }

        .cert-number {
            text-align: right;
            font-size: 12px;
        }

        .line {
            border-bottom: 1px solid #000;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Certificat de Perte</div>
    </div>

    <div class="cert-number">
        Numéro du Certificat : <strong>{{ $certificat->certificat_number }}</strong><br>
        Date : {{ \Carbon\Carbon::parse($certificat->created_at)->format('d/m/Y') }}
    </div>

    <div class="line"></div>

    <div class="section">
        <p>Le présent certificat atteste que <strong>{{ $certificat->declarationDePerte->FirstNameInDoc }} {{ $certificat->declarationDePerte->LastNameInDoc }}</strong> a effectué une déclaration de perte pour le document suivant :</p>
        <ul>
           <li><strong>Titre de la déclaration :</strong> {{ $certificat->declarationDePerte->Title }}</li>
           <li><strong>Type de document :</strong> {{ $certificat->documentType->TypeName ?? 'Inconnu' }}</li>
            @if($certificat->declarationDePerte->DocIdentification)
            <li><strong>Numéro du document :</strong> {{ $certificat->declarationDePerte->DocIdentification }}</li>
            @endif
        </ul>
    </div>

    <div class="section">
        <p>{{ $certificat->description }}</p>
    </div>

    <div class="footer">
        Ce certificat a été généré automatiquement par la plateforme DocTrack<br>
        (https://sendoctrack.netlify.app)
    </div>
</body>
</html> --}}


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat de Perte</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 40px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }

        .header img {
            height: 80px;
        }

        .republique {
            text-align: center;
            font-size: 14px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            text-decoration: underline;
        }

        .section {
            margin-top: 30px;
        }

        .section h3 {
            font-size: 16px;
            margin-bottom: 10px;
            text-decoration: underline;
        }

        .section ul {
            list-style: none;
            padding-left: 0;
        }

        .section ul li {
            margin-bottom: 8px;
        }

        .footer {
            margin-top: 50px;
            text-align: right;
            font-size: 12px;
        }
    </style>
</head>
<body>

    {{-- En-tête avec le drapeau et la mention du ministère --}}
    <div class="header">
        <div class="header-content">
            <img src="{{ public_path('images/drapeau.png') }}" alt="Drapeau du Sénégal">
            <div class="republique">
                <strong>République du Sénégal</strong><br>
                Ministère de l’Intérieur et de la Sécurité Publique
            </div>
        </div>
        <div class="title">Certificat de Perte</div>
    </div>

    {{-- Informations sur la déclaration --}}
    <div class="section">
        <p>Le présent certificat atteste que <strong>{{ $certificat->declarationDePerte->FirstNameInDoc }} {{ $certificat->declarationDePerte->LastNameInDoc }}</strong> a effectué une déclaration de perte pour son document dont le type est  :<strong> {{ $certificat->documentType->TypeName ?? 'Inconnu' }}</strong> avec comme
        <strong>Numéro du certificat :</strong> <strong>{{ $certificat->certificat_number }} </strong></p>
    </div>

    <div class="footer">
        Fait à Dakar, le {{ \Carbon\Carbon::now()->format('d/m/Y') }}<br>
        <strong>Signature et cachet de l’administration</strong>
    </div>

</body>
</html>
