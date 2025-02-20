<?php

namespace Services;

use InvalidArgumentException;

/**
 * Service pour gérer les paiements SCI via BarkaPay.
 *
 * Cette classe permet de créer des liens de paiement pour des transactions mobiles
 * via le service SCI (Service Client Intégré) de BarkaPay.
 */
class SCIBarkaPayPaymentService extends BaseBarkaPayPaymentService
{
    /**
     * Crée un lien de paiement pour un paiement mobile via le SCI.
     *
     * @param array $paymentData Détails du paiement. Les champs requis sont :
     *                           - 'title' (string) : Le titre du paiement.
     *                           - 'amount' (float|string) : Le montant fixe du paiement.
     *                           - 'order_id' (string) : Identifiant unique de la commande.
     *                           - 'callback_url' (string) : URL de rappel en cas de succès.
     *                           - 'pay_url' (string) : URL redirigeant après un paiement réussi.
     *                           - 'no_pay_url' (string) : URL redirigeant après un échec de paiement.
     * @param string $language La langue de préférence pour la réponse API ('fr' par défaut).
     *
     * @return object Réponse de l'API contenant les détails du lien de paiement créé.
     *
     * @throws InvalidArgumentException Si des champs requis sont manquants.
    //  * @throws RuntimeException En cas d'échec de la requête HTTP.
     */
    public function createPaymentLink(array $paymentData, string $language = 'fr')
    {
        // Vérifie que tous les champs requis sont fournis
        if (
            empty($paymentData['title']) || empty($paymentData['amount']) || empty($paymentData['order_id'])
            || empty($paymentData['callback_url']) || empty($paymentData['pay_url']) || empty($paymentData['no_pay_url'])
        ) {
            throw new InvalidArgumentException("Missing required fields: title, amount, order_id, callback_url, pay_url, or no_pay_url.");
        }

        // URL de l'API pour créer un lien de paiement SCI
        $url = self::BASE_URL . 'payment/mobile/web/create';

        // Ajoute une valeur fixe pour le champ 'set_amount'
        $paymentData['set_amount'] = 'fixed';

        $paymentData['period'] = $paymentData['period'] ?? 1500;

        // Envoie la requête HTTP en utilisant la méthode SCI dédiée
        return $this->sendSCIHttpRequest(self::METHOD_POST, $url, ['Accept-Language' => $language], $paymentData);
    }
}
