<?php

require 'vendor/autoload.php';

use Services\APIBarkaPayPaymentService;

try {
    // Instancie le service avec les clés API via .env
    $transferService = new APIBarkaPayPaymentService();

    // Données de transfert à envoyer
    $transferDetails = [
        'receiver_country' => 'BFA',
        'receiver_phonenumber' => '72929595',
        'operator' => 'MOOV',
        'amount' => '1000',
        'note' => 'Ambition is priceless.',
        'order_id' => 'iweidiewd743kwedk',
        'callback_url' => 'https://www.app.barkachange.com/barkapay-71737188',
        'order_data' => json_encode(['order_data' => 'no-data']),
        'ignore_double_spend_risk' => '1',
    ];

    // Appelle la méthode createMobileTransfer
    $response = $transferService->createMobileTransfer($transferDetails);

    // Affiche la réponse de l'API
    print_r($response);

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
