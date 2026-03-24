<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $meta['type'] }} DocTrack — {{ $stats['periode'] }}</title>
    <style>
        /* =====================================================================
           RESET & BASE
        ===================================================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9.5pt;
            color: #1a1a2e;
            background: #ffffff;
            line-height: 1.55;
        }

        /* =====================================================================
           VARIABLES DE COULEUR (simulées via classes)
        ===================================================================== */
        /* Palette officielle DocTrack */
        /* Primaire : #0f3460 (bleu marine institutionnel) */
        /* Accent   : #16213e  */
        /* Or       : #e2b04a  */
        /* Vert     : #16a34a  */
        /* Orange   : #ea580c  */
        /* Rouge    : #dc2626  */
        /* Gris     : #64748b  */
        /* Fond     : #f8fafc  */

        /* =====================================================================
           PAGE
        ===================================================================== */
        @page {
            margin: 0;
            size: A4 portrait;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            position: relative;
            background: #ffffff;
        }

        /* =====================================================================
           EN-TÊTE OFFICIEL
        ===================================================================== */
        .header {
            background: #0f3460;
            padding: 0;
            position: relative;
            overflow: hidden;
        }

        .header-bande-or {
            height: 5px;
            background: linear-gradient(90deg, #e2b04a 0%, #f0c96a 50%, #e2b04a 100%);
        }

        .header-content {
            padding: 18px 28px 16px 28px;
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 65%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 35%;
        }

        .header-republique {
            font-size: 7pt;
            color: #a8c4e0;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .header-pays {
            font-size: 8pt;
            color: #e2b04a;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .header-plateforme {
            font-size: 22pt;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 2px;
            line-height: 1;
        }

        .header-plateforme span {
            color: #e2b04a;
        }

        .header-slogan {
            font-size: 7.5pt;
            color: #90aec4;
            margin-top: 4px;
            font-style: italic;
        }

        .header-badge-type {
            display: inline-block;
            background: #e2b04a;
            color: #0f3460;
            font-size: 7pt;
            font-weight: bold;
            padding: 3px 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-radius: 2px;
            margin-bottom: 6px;
        }

        .header-numero {
            font-size: 7pt;
            color: #90aec4;
            margin-bottom: 2px;
        }

        .header-date {
            font-size: 7pt;
            color: #a8c4e0;
        }

        .header-bande-bas {
            height: 3px;
            background: linear-gradient(90deg, #e2b04a, #0f3460 40%);
        }

        /* =====================================================================
           BANDEAU PÉRIODE + NIVEAU GLOBAL
        ===================================================================== */
        .bandeau-periode {
            background: #16213e;
            padding: 10px 28px;
            display: table;
            width: 100%;
        }

        .bandeau-left {
            display: table-cell;
            vertical-align: middle;
        }

        .bandeau-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .bandeau-label {
            font-size: 7pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .bandeau-periode-nom {
            font-size: 13pt;
            font-weight: bold;
            color: #ffffff;
        }

        .bandeau-niveau {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .niveau-satisfaisant { background: #16a34a; color: #fff; }
        .niveau-acceptable    { background: #ca8a04; color: #fff; }
        .niveau-preoccupant   { background: #ea580c; color: #fff; }
        .niveau-critique      { background: #dc2626; color: #fff; }

        /* =====================================================================
           CORPS PRINCIPAL
        ===================================================================== */
        .body-content {
            padding: 20px 28px;
        }

        /* =====================================================================
           SECTIONS
        ===================================================================== */
        .section {
            margin-bottom: 20px;
        }

        .section-titre {
            font-size: 9pt;
            font-weight: bold;
            color: #0f3460;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-bottom: 2px solid #0f3460;
            padding-bottom: 4px;
            margin-bottom: 12px;
            display: table;
            width: 100%;
        }

        .section-titre-accent {
            display: table-cell;
        }

        .section-titre-numero {
            display: table-cell;
            text-align: right;
            font-size: 7pt;
            color: #94a3b8;
            font-weight: normal;
            letter-spacing: 0;
        }

        /* =====================================================================
           CARTES KPI — CHIFFRES CLÉS
        ===================================================================== */
        .kpi-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
        }

        .kpi-row {
            display: table-row;
        }

        .kpi-cell {
            display: table-cell;
            width: 25%;
        }

        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #0f3460;
            padding: 10px 12px;
            border-radius: 2px;
        }

        .kpi-card.accent-or    { border-top-color: #e2b04a; }
        .kpi-card.accent-vert  { border-top-color: #16a34a; }
        .kpi-card.accent-rouge { border-top-color: #dc2626; }
        .kpi-card.accent-bleu  { border-top-color: #0f3460; }

        .kpi-valeur {
            font-size: 20pt;
            font-weight: bold;
            color: #0f3460;
            line-height: 1;
            margin-bottom: 2px;
        }

        .kpi-valeur.couleur-vert  { color: #16a34a; }
        .kpi-valeur.couleur-rouge { color: #dc2626; }
        .kpi-valeur.couleur-or    { color: #e2b04a; }

        .kpi-label {
            font-size: 7pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .kpi-variation {
            font-size: 7pt;
            margin-top: 4px;
            padding: 2px 6px;
            border-radius: 2px;
            display: inline-block;
        }

        .var-hausse { background: #fef2f2; color: #dc2626; }
        .var-baisse { background: #f0fdf4; color: #16a34a; }
        .var-stable { background: #f8fafc; color: #64748b; }

        /* =====================================================================
           CONSTAT GÉNÉRAL
        ===================================================================== */
        .constat-box {
            background: #f0f6ff;
            border-left: 4px solid #0f3460;
            padding: 12px 16px;
            border-radius: 0 3px 3px 0;
        }

        .constat-text {
            font-size: 9pt;
            color: #1e3a5f;
            line-height: 1.7;
            text-align: justify;
        }

        /* =====================================================================
           TABLEAU PAR TYPE
        ===================================================================== */
        .table-officielle {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }

        .table-officielle thead tr {
            background: #0f3460;
            color: #ffffff;
        }

        .table-officielle thead th {
            padding: 7px 10px;
            text-align: left;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
            font-weight: bold;
        }

        .table-officielle thead th.right { text-align: right; }

        .table-officielle tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .table-officielle tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .table-officielle tbody td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        .table-officielle tbody td.right {
            text-align: right;
            font-weight: bold;
        }

        .table-officielle tbody td.center {
            text-align: center;
        }

        .table-officielle tfoot tr {
            background: #16213e;
            color: #ffffff;
        }

        .table-officielle tfoot td {
            padding: 6px 10px;
            font-weight: bold;
            font-size: 8pt;
        }

        .table-officielle tfoot td.right { text-align: right; }

        /* =====================================================================
           BARRE DE PROGRESSION
        ===================================================================== */
        .barre-container {
            background: #e2e8f0;
            border-radius: 2px;
            height: 8px;
            width: 100%;
            display: inline-block;
            vertical-align: middle;
        }

        .barre-fill {
            height: 8px;
            border-radius: 2px;
            background: #0f3460;
        }

        .barre-fill.vert  { background: #16a34a; }
        .barre-fill.rouge { background: #dc2626; }
        .barre-fill.or    { background: #e2b04a; }

        /* =====================================================================
           ALERTES
        ===================================================================== */
        .alertes-liste {
            margin: 0;
            padding: 0;
        }

        .alerte-item {
            padding: 9px 12px;
            margin-bottom: 6px;
            border-radius: 3px;
            border-left: 4px solid;
            display: table;
            width: 100%;
        }

        .alerte-critique     { background: #fef2f2; border-color: #dc2626; }
        .alerte-avertissement{ background: #fff7ed; border-color: #ea580c; }
        .alerte-information  { background: #eff6ff; border-color: #3b82f6; }

        .alerte-icone {
            display: table-cell;
            width: 20px;
            vertical-align: top;
            font-size: 9pt;
            padding-top: 1px;
        }

        .alerte-body {
            display: table-cell;
            vertical-align: top;
        }

        .alerte-titre {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .alerte-message {
            font-size: 8pt;
            color: #475569;
            line-height: 1.5;
        }

        /* =====================================================================
           POINTS POSITIFS
        ===================================================================== */
        .positif-item {
            padding: 7px 12px;
            margin-bottom: 5px;
            background: #f0fdf4;
            border-left: 4px solid #16a34a;
            border-radius: 3px;
            display: table;
            width: 100%;
        }

        .positif-icone {
            display: table-cell;
            width: 20px;
            vertical-align: top;
            font-size: 9pt;
        }

        .positif-text {
            display: table-cell;
            font-size: 8.5pt;
            color: #166534;
            line-height: 1.5;
            vertical-align: top;
        }

        /* =====================================================================
           ÉVOLUTION — GRAPHIQUE EN BARRES SVG-LIKE (CSS only)
        ===================================================================== */
        .evolution-table {
            width: 100%;
            border-collapse: collapse;
        }

        .evolution-table td {
            vertical-align: bottom;
            text-align: center;
            padding: 0 2px;
            font-size: 6.5pt;
            color: #64748b;
        }

        .barre-evolution {
            width: 100%;
            background: #e2e8f0;
            border-radius: 2px 2px 0 0;
            position: relative;
            margin: 0 auto;
        }

        .barre-dec {
            background: #0f3460;
            border-radius: 2px 2px 0 0;
            width: 100%;
        }

        .barre-pub {
            background: #e2b04a;
            border-radius: 2px 2px 0 0;
            width: 100%;
        }

        /* =====================================================================
           ANALYSE TEXTUELLE
        ===================================================================== */
        .analyse-bloc {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 11px 14px;
            margin-bottom: 8px;
        }

        .analyse-bloc-titre {
            font-size: 7.5pt;
            font-weight: bold;
            color: #0f3460;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 5px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
        }

        .analyse-bloc-texte {
            font-size: 8.5pt;
            color: #334155;
            line-height: 1.65;
            text-align: justify;
        }

        /* =====================================================================
           RECOMMANDATIONS
        ===================================================================== */
        .reco-item {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
        }

        .reco-numero {
            display: table-cell;
            width: 28px;
            background: #0f3460;
            color: #e2b04a;
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 10px 0;
        }

        .reco-body {
            display: table-cell;
            padding: 8px 12px;
            vertical-align: top;
            background: #f8fafc;
        }

        .reco-titre {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f3460;
            margin-bottom: 3px;
        }

        .reco-texte {
            font-size: 8pt;
            color: #475569;
            line-height: 1.55;
            text-align: justify;
        }

        .reco-acteurs {
            margin-top: 5px;
        }

        .reco-acteur-tag {
            display: inline-block;
            background: #e0eaff;
            color: #1e40af;
            font-size: 6.5pt;
            padding: 2px 7px;
            border-radius: 10px;
            margin-right: 4px;
        }

        /* =====================================================================
           PIED DE PAGE
        ===================================================================== */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .footer-bande {
            height: 3px;
            background: linear-gradient(90deg, #0f3460, #e2b04a 50%, #0f3460);
        }

        .footer-content {
            background: #16213e;
            padding: 6px 28px;
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            vertical-align: middle;
        }

        .footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .footer-text {
            font-size: 6.5pt;
            color: #64748b;
        }

        .footer-confidentiel {
            font-size: 6.5pt;
            color: #e2b04a;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* =====================================================================
           SÉPARATEURS & UTILITAIRES
        ===================================================================== */
        .sep {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 14px 0;
        }

        .sep-leger {
            border: none;
            border-top: 1px dashed #e2e8f0;
            margin: 8px 0;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-muted  { color: #94a3b8; font-size: 7.5pt; }
        .bold        { font-weight: bold; }
        .mt-8        { margin-top: 8px; }
        .mt-4        { margin-top: 4px; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 2px;
            font-size: 7pt;
            font-weight: bold;
        }

        .badge-bleu  { background: #dbeafe; color: #1e40af; }
        .badge-vert  { background: #dcfce7; color: #15803d; }
        .badge-rouge { background: #fee2e2; color: #b91c1c; }
        .badge-or    { background: #fef3c7; color: #92400e; }

        /* Forcer les sauts de page */
        .page-break { page-break-after: always; }
        .no-break   { page-break-inside: avoid; }

        /* Deux colonnes */
        .col-2 {
            display: table;
            width: 100%;
            border-spacing: 10px;
            border-collapse: separate;
        }

        .col-2-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════════════════════
     PIED DE PAGE FIXE (déclaré en premier pour dompdf)
═══════════════════════════════════════════════════════════════════════════ -->
<div class="footer">
    <div class="footer-bande"></div>
    <div class="footer-content">
        <div class="footer-left">
            <div class="footer-text">
                Rapport généré automatiquement par DocTrack — {{ $meta['genere_le'] }}
            </div>
            <div class="footer-text">
                Réf. {{ $meta['numero_rapport'] }} | {{ $meta['pays'] }}
            </div>
        </div>
        <div class="footer-right">
            <div class="footer-confidentiel">Document officiel</div>
            <div class="footer-text">Usage institutionnel</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     PAGE 1
═══════════════════════════════════════════════════════════════════════════ -->
<div class="page">

    <!-- EN-TÊTE -->
    <div class="header">
        <div class="header-bande-or"></div>
        <div class="header-content">
            <div class="header-left">
                <div class="header-republique">Plateforme nationale de gestion documentaire</div>
                <div class="header-pays">{{ $meta['pays'] }}</div>
                <div class="header-plateforme">Doc<span>Track</span></div>
                <div class="header-slogan">Retrouver vos documents perdus, partout au Sénégal</div>
            </div>
            <div class="header-right">
                <div class="header-badge-type">{{ $meta['type'] }}</div>
                <div class="header-numero">Réf. {{ $meta['numero_rapport'] }}</div>
                <div class="header-date">Généré le {{ $meta['genere_le'] }}</div>
            </div>
        </div>
        <div class="header-bande-bas"></div>
    </div>

    <!-- BANDEAU PÉRIODE + NIVEAU -->
    <div class="bandeau-periode">
        <div class="bandeau-left">
            <div class="bandeau-label">Période analysée</div>
            <div class="bandeau-periode-nom">{{ $stats['periode'] }}</div>
        </div>
        <div class="bandeau-right">
            <span class="bandeau-niveau niveau-{{ $analyse['niveau_global']['niveau'] }}">
                {{ $analyse['niveau_global']['icone'] }} {{ $analyse['niveau_global']['label'] }}
            </span>
        </div>
    </div>

    <!-- CORPS -->
    <div class="body-content">

        <!-- ══════════════════════════════════════════════════════════════════
             SECTION 1 — CHIFFRES CLÉS
        ═══════════════════════════════════════════════════════════════════ -->
        <div class="section">
            <div class="section-titre">
                <span class="section-titre-accent">1. Chiffres clés de la période</span>
                <span class="section-titre-numero">§1</span>
            </div>

            <div class="kpi-grid">
                <div class="kpi-row">
                    <!-- KPI 1 : Déclarations -->
                    <div class="kpi-cell">
                        <div class="kpi-card accent-bleu">
                            <div class="kpi-valeur">{{ number_format($stats['declarations']['total'], 0, ',', ' ') }}</div>
                            <div class="kpi-label">Déclarations de perte</div>
                            @php $varD = $stats['comparaison']['declarations_variation']; @endphp
                            @if($varD['sens'] !== 'neutre')
                                <div class="kpi-variation var-{{ $varD['sens'] }}">
                                    {{ $varD['sens'] === 'hausse' ? '↑' : ($varD['sens'] === 'baisse' ? '↓' : '→') }}
                                    {{ $varD['valeur'] }}% vs période préc.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- KPI 2 : Publications -->
                    <div class="kpi-cell">
                        <div class="kpi-card accent-or">
                            <div class="kpi-valeur couleur-or">{{ number_format($stats['publications']['total'], 0, ',', ' ') }}</div>
                            <div class="kpi-label">Documents publiés</div>
                            @php $varP = $stats['comparaison']['publications_variation']; @endphp
                            @if($varP['sens'] !== 'neutre')
                                <div class="kpi-variation var-{{ $varP['sens'] }}">
                                    {{ $varP['sens'] === 'hausse' ? '↑' : ($varP['sens'] === 'baisse' ? '↓' : '→') }}
                                    {{ $varP['valeur'] }}% vs période préc.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- KPI 3 : Taux de restitution -->
                    <div class="kpi-cell">
                        <div class="kpi-card accent-vert">
                            <div class="kpi-valeur {{ $stats['publications']['taux_restitution'] >= 50 ? 'couleur-vert' : ($stats['publications']['taux_restitution'] < 25 ? 'couleur-rouge' : '') }}">
                                {{ $stats['publications']['taux_restitution'] }}%
                            </div>
                            <div class="kpi-label">Taux de restitution</div>
                            <div class="kpi-variation var-stable">
                                {{ $stats['publications']['recuperes'] }} doc(s) restitué(s)
                            </div>
                        </div>
                    </div>

                    <!-- KPI 4 : Localités couvertes -->
                    <div class="kpi-cell">
                        <div class="kpi-card accent-rouge">
                            <div class="kpi-valeur">{{ $stats['par_region']['nombre_localites'] }}</div>
                            <div class="kpi-label">Localités couvertes</div>
                            <div class="kpi-variation var-stable">
                                {{ $stats['utilisateurs']['actifs'] }} utilisateur(s) actif(s)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════
             SECTION 2 — CONSTAT GÉNÉRAL
        ═══════════════════════════════════════════════════════════════════ -->
        <div class="section">
            <div class="section-titre">
                <span class="section-titre-accent">2. Constat général</span>
                <span class="section-titre-numero">§2</span>
            </div>
            <div class="constat-box">
                <div class="constat-text">{{ $analyse['constat_general'] }}</div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════
             SECTION 3 — STATISTIQUES DÉTAILLÉES PAR TYPE
        ═══════════════════════════════════════════════════════════════════ -->
        <div class="section no-break">
            <div class="section-titre">
                <span class="section-titre-accent">3. Répartition par type de document</span>
                <span class="section-titre-numero">§3</span>
            </div>

            <div class="col-2">
                <!-- Déclarations par type -->
                <div class="col-2-cell">
                    <p class="text-muted bold" style="margin-bottom:6px;">Déclarations de perte</p>
                    <table class="table-officielle">
                        <thead>
                            <tr>
                                <th>Type de document</th>
                                <th class="right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['par_type']['declarations'] as $item)
                            <tr>
                                <td>{{ $item['type'] }}</td>
                                <td class="right">{{ $item['total'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Aucune déclaration</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>TOTAL</td>
                                <td class="right">{{ $stats['declarations']['total'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Publications par type -->
                <div class="col-2-cell">
                    <p class="text-muted bold" style="margin-bottom:6px;">Documents trouvés publiés</p>
                    <table class="table-officielle">
                        <thead>
                            <tr>
                                <th>Type de document</th>
                                <th class="right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['par_type']['publications'] as $item)
                            <tr>
                                <td>{{ $item['type'] }}</td>
                                <td class="right">{{ $item['total'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">Aucune publication</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>TOTAL</td>
                                <td class="right">{{ $stats['publications']['total'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════
             SECTION 4 — ANALYSE GÉOGRAPHIQUE
        ═══════════════════════════════════════════════════════════════════ -->
        <div class="section no-break">
            <div class="section-titre">
                <span class="section-titre-accent">4. Analyse géographique</span>
                <span class="section-titre-numero">§4</span>
            </div>

            <!-- Texte d'analyse -->
            <div class="analyse-bloc" style="margin-bottom:10px;">
                <div class="analyse-bloc-titre">Synthèse territoriale</div>
                <div class="analyse-bloc-texte">{{ $analyse['analyse_geographique'] }}</div>
            </div>

            <!-- Tableau des régions -->
            @if($stats['par_region']['liste']->count() > 0)
            <table class="table-officielle">
                <thead>
                    <tr>
                        <th>Localité</th>
                        <th class="right">Publications</th>
                        <th class="right">Restitués</th>
                        <th class="right">Non restitués</th>
                        <th class="right">Taux</th>
                        <th>Progression</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['par_region']['liste'] as $region)
                    @php
                        $taux = $region['taux_restitution'];
                        $barreClass = $taux >= 50 ? 'vert' : ($taux < 25 ? 'rouge' : 'or');
                        $largeur = min($taux, 100);
                    @endphp
                    <tr>
                        <td>
                            {{ $region['region'] }}
                            @if(isset($stats['par_region']['zones_critiques']) && $stats['par_region']['zones_critiques']->contains('region', $region['region']))
                                <span class="badge badge-rouge">Zone critique</span>
                            @endif
                        </td>
                        <td class="right">{{ $region['total_publications'] }}</td>
                        <td class="right">{{ $region['recuperes'] }}</td>
                        <td class="right">{{ $region['non_recuperes'] }}</td>
                        <td class="right">
                            <span class="badge {{ $taux >= 50 ? 'badge-vert' : ($taux < 25 ? 'badge-rouge' : 'badge-or') }}">
                                {{ $taux }}%
                            </span>
                        </td>
                        <td style="width:80px; padding-right:12px;">
                            <div class="barre-container">
                                <div class="barre-fill {{ $barreClass }}" style="width:{{ $largeur }}%;"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

    </div><!-- /body-content page 1 -->
</div><!-- /page 1 -->

<!-- ═══════════════════════════════════════════════════════════════════════════
     PAGE 2
═══════════════════════════════════════════════════════════════════════════ -->
<div class="page-break"></div>
<div class="page">
    <div class="body-content">

        <!-- ══════════════════════════════════════════════════════════════════
             SECTION 5 — ÉVOLUTION TEMPORELLE
        ═══════════════════════════════════════════════════════════════════ -->
        <div class="section no-break">
            <div class="section-titre">
                <span class="section-titre-accent">
                    5. Évolution {{ $stats['evolution']['type'] === 'mensuelle' ? 'mensuelle' : 'journalière' }}
                </span>
                <span class="section-titre-numero">§5</span>
            </div>

            <!-- Analyse temporelle -->
            <div class="analyse-bloc" style="margin-bottom:10px;">
                <div class="analyse-bloc-titre">Tendance observée</div>
                <div class="analyse-bloc-texte">{{ $analyse['analyse_temporelle'] }}</div>
            </div>

            <!-- Graphique en barres CSS -->
            @php
                $evolutionData = $stats['evolution']['donnees'];
                $maxVal = collect($evolutionData)->max(fn($d) => max($d['declarations'], $d['publications']));
                $maxVal = max($maxVal, 1);
                $hauteurMax = 50; // px max pour les barres
            @endphp

            <table class="evolution-table">
                <tr>
                    @foreach($evolutionData as $point)
                    @php
                        $hDec = round(($point['declarations'] / $maxVal) * $hauteurMax);
                        $hPub = round(($point['publications'] / $maxVal) * $hauteurMax);
                    @endphp
                    <td style="width:{{ round(100 / count($evolutionData), 2) }}%;">
                        <!-- Barre déclarations -->
                        <div style="height:{{ $hauteurMax }}px; position:relative; display:flex; align-items:flex-end; justify-content:center; gap:1px;">
                            <div class="barre-dec" style="height:{{ $hDec }}px; width:45%;"></div>
                            <div class="barre-pub" style="height:{{ $hPub }}px; width:45%;"></div>
                        </div>
                        <div style="font-size:5.5pt; color:#94a3b8; text-align:center; margin-top:2px;">
                            {{ is_string($point['label']) ? substr($point['label'], 0, 3) : $point['label'] }}
                        </div>
                    </td>
                    @endforeach
                </tr>
            </table>

            <!-- Légende -->
            <div class="mt-4" style="text-align:center;">
                <span style="display:inline-block; width:10px; height:10px; background:#0f3460; border-radius:1px; margin-right:4px; vertical-align:middle;"></span>
                <span class="text-muted">Déclarations</span>
                &nbsp;&nbsp;
                <span style="display:inline-block; width:10px; height:10px; background:#e2b04a; border-radius:1px; margin-right:4px; vertical-align:middle;"></span>
                <span class="text-muted">Publications</span>
            </div>

            <!-- Tableau récapitulatif évolution -->
            <div class="mt-8">
                <table class="table-officielle">
                    <thead>
                        <tr>
                            <th>{{ $stats['evolution']['type'] === 'mensuelle' ? 'Mois' : 'Jour' }}</th>
                            <th class="right">Déclarations</th>
                            <th class="right">Publications</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evolutionData as $point)
                        @if($point['declarations'] > 0 || $point['publications'] > 0)
                        <tr>
                            <td>{{ $point['label'] }}</td>
                            <td class="right">{{ $point['declarations'] }}</td>
                            <td class="right">{{ $point['publications'] }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <hr class="sep">

        <!-- ══════════════════════════════════════════════════════════════════
             SECTION 6 — ALERTES & POINTS POSITIFS
        ═══════════════════════════════════════════════════════════════════ -->
        <div class="section no-break">
            <div class="section-titre">
                <span class="section-titre-accent">6. Alertes et points d'attention</span>
                <span class="section-titre-numero">§6</span>
            </div>

            <div class="col-2">
                <!-- Alertes -->
                <div class="col-2-cell">
                    <p class="text-muted bold" style="margin-bottom:6px;">Points d'attention</p>
                    @forelse($analyse['alertes'] as $alerte)
                    <div class="alerte-item alerte-{{ $alerte['niveau'] }} no-break">
                        <div class="alerte-icone">{{ $alerte['icone'] }}</div>
                        <div class="alerte-body">
                            <div class="alerte-titre">{{ $alerte['titre'] }}</div>
                            <div class="alerte-message">{{ $alerte['message'] }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="analyse-bloc">
                        <div class="analyse-bloc-texte text-center">✅ Aucune alerte pour cette période.</div>
                    </div>
                    @endforelse
                </div>

                <!-- Points positifs -->
                <div class="col-2-cell">
                    <p class="text-muted bold" style="margin-bottom:6px;">Points positifs</p>
                    @forelse($analyse['points_positifs'] as $positif)
                    <div class="positif-item no-break">
                        <div class="positif-icone">{{ $positif['icone'] }}</div>
                        <div class="positif-text">{{ $positif['message'] }}</div>
                    </div>
                    @empty
                    <div class="analyse-bloc">
                        <div class="analyse-bloc-texte text-center text-muted">Aucun point positif identifié.</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <hr class="sep">

        <!-- ══════════════════════════════════════════════════════════════════
             SECTION 7 — RECOMMANDATIONS
        ═══════════════════════════════════════════════════════════════════ -->
        <div class="section">
            <div class="section-titre">
                <span class="section-titre-accent">7. Recommandations</span>
                <span class="section-titre-numero">§7</span>
            </div>

            @foreach($analyse['recommandations'] as $reco)
            <div class="reco-item no-break">
                <div class="reco-numero">{{ $reco['priorite'] }}</div>
                <div class="reco-body">
                    <div class="reco-titre">{{ $reco['titre'] }}</div>
                    <div class="reco-texte">{{ $reco['texte'] }}</div>
                    @if(!empty($reco['acteurs']))
                    <div class="reco-acteurs">
                        @foreach($reco['acteurs'] as $acteur)
                        <span class="reco-acteur-tag">{{ $acteur }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <hr class="sep">

        <!-- ══════════════════════════════════════════════════════════════════
             SECTION 8 — DONNÉES COMPLÉMENTAIRES
        ═══════════════════════════════════════════════════════════════════ -->
        <div class="section no-break">
            <div class="section-titre">
                <span class="section-titre-accent">8. Données complémentaires</span>
                <span class="section-titre-numero">§8</span>
            </div>

            <div class="col-2">
                <!-- Restitutions -->
                <div class="col-2-cell">
                    <div class="analyse-bloc">
                        <div class="analyse-bloc-titre">Restitutions</div>
                        <table style="width:100%; font-size:8.5pt;">
                            <tr>
                                <td style="padding:4px 0; color:#475569;">Demandes de restitution</td>
                                <td style="text-align:right; font-weight:bold; color:#0f3460;">
                                    {{ $stats['restitutions']['demandes_restitution'] }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0; color:#475569;">Documents effectivement récupérés</td>
                                <td style="text-align:right; font-weight:bold; color:#16a34a;">
                                    {{ $stats['restitutions']['documents_recuperes'] }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0; color:#475569;">Documents non récupérés</td>
                                <td style="text-align:right; font-weight:bold; color:#dc2626;">
                                    {{ $stats['publications']['non_recuperes'] }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Utilisateurs -->
                <div class="col-2-cell">
                    <div class="analyse-bloc">
                        <div class="analyse-bloc-titre">Utilisateurs</div>
                        <table style="width:100%; font-size:8.5pt;">
                            <tr>
                                <td style="padding:4px 0; color:#475569;">Nouveaux inscrits sur la période</td>
                                <td style="text-align:right; font-weight:bold; color:#0f3460;">
                                    {{ $stats['utilisateurs']['nouveaux_inscrits'] }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0; color:#475569;">Utilisateurs actifs</td>
                                <td style="text-align:right; font-weight:bold; color:#16a34a;">
                                    {{ $stats['utilisateurs']['actifs'] }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0; color:#475569;">Déclarations supprimées (soft)</td>
                                <td style="text-align:right; font-weight:bold; color:#64748b;">
                                    {{ $stats['declarations']['supprimees'] }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════
             CONCLUSION
        ═══════════════════════════════════════════════════════════════════ -->
        <div class="section no-break">
            <div class="section-titre">
                <span class="section-titre-accent">Conclusion</span>
            </div>
            <div class="constat-box" style="background:#f0f9ff; border-color:#e2b04a;">
                <div class="constat-text">
                    Ce rapport institutionnel {{ strtolower($meta['type']) }} DocTrack couvre la période
                    <strong>{{ $stats['periode'] }}</strong> et synthétise l'ensemble des activités
                    enregistrées sur la plateforme nationale de déclaration et de restitution de documents perdus.
                    Les données présentées sont extraites automatiquement du système DocTrack
                    (réf. <strong>{{ $meta['numero_rapport'] }}</strong>).
                    @if(count($analyse['recommandations']) > 0)
                        Un total de <strong>{{ count($analyse['recommandations']) }} recommandation(s)</strong>
                        ont été formulées pour améliorer les performances du dispositif sur la prochaine période.
                    @endif
                    Ce document est à usage institutionnel et destiné aux autorités compétentes
                    de la <strong>{{ $meta['pays'] }}</strong>.
                </div>
            </div>

            <!-- Bloc signature -->
            <div style="margin-top:24px; display:table; width:100%;">
                <div style="display:table-cell; width:50%; vertical-align:top; padding-right:20px;">
                    <div style="border-top:1px solid #cbd5e1; padding-top:6px;">
                        <div class="text-muted">Généré par le système DocTrack</div>
                        <div class="text-muted">{{ $meta['genere_le'] }}</div>
                    </div>
                </div>
                <div style="display:table-cell; width:50%; vertical-align:top; text-align:right; padding-left:20px;">
                    <div style="border-top:1px solid #cbd5e1; padding-top:6px;">
                        <div class="text-muted">Visa autorité compétente</div>
                        <div class="text-muted">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /body-content page 2 -->
</div><!-- /page 2 -->

</body>
</html>
