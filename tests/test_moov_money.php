<?php

require 'vendor/autoload.php';

use Services\Specs\MoovMoneyBFBarkaPayPaymentService;

try {
    // Instancie le service de paiement Moov Money pour Burkina Faso
    $moovMoneyService = new MoovMoneyBFBarkaPayPaymentService();

    // Détails de paiement
    $paymentDetails = [
        'sender_phonenumber' => '62002208',     // Numéro de téléphone valide
        'amount' => 100,                      // Montant du paiement
        'order_id' => 'ORDER123',               // Identifiant unique de la commande
        'callback_url' => 'https://e-events.net/notify'
    ];

    // Appelle la méthode initMobilePayment et affiche la réponse
    $response = $moovMoneyService->initMobilePayment($paymentDetails);

    // Affiche la réponse
    print_r($response);

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
