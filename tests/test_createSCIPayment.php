<?php

require 'vendor/autoload.php';

use Services\SCIBarkaPayPaymentService;

try {
    // Instancie le service avec les clés API via .env
    $paymentService = new SCIBarkaPayPaymentService();

    // Données de transfert à envoyer
    $paymentDetails = [
        'title' => "Achat d'un ticket",
        'amount' => '100',
        'order_id' => 'iweidiewd743kwedk',
        'callback_url' => 'https://website.e-events.net',
        'pay_url' => 'https://website.e-events.net',
        'no_pay_url' => 'https://website.e-events.net',
        'ignore_double_spend_risk' => '1',
    ];

    $response = $paymentService->createPaymentLink($paymentDetails);

    // Affiche la réponse de l'API
    print_r($response);
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
