@component('mail::message')
# Notification de Publication d'un Document

Bonjour,

Suite à votre déclaration de perte, nous avons le plaisir de vous informer que le document portant le prénom **{{ $document->OwnerFirstName }} et le nom {{ $document->OwnerLastName }}** a été récemment publié. Il pourrait correspondre à votre déclaration de perte.

Pour toute question ou pour obtenir plus d'informations, nous vous encourageons à contacter directement l'auteur de la publication. Voici son numéro de téléphone : **{{ $Phone }}**.

@component('mail::button', ['url' => $documentUrl])
Voir la publication
@endcomponent

Nous vous remercions de votre attention et restons à votre disposition pour toute assistance complémentaire.

Cordialement,<br>
{{ config('app.name') }}
@endcomponent
