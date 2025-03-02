@component('mail::message')
Bonjour Monsieur/Madame
Votre document a été trouvé par quelqu’un. Vous pouvez contacter le trouveur au numéro suivant : {{ $finderPhone }}
Nous vous remercions de votre attention et restons à votre disposition pour toute assistance complémentaire.
Cordialement,<br>
{{ config('app.name') }}
@endcomponent
