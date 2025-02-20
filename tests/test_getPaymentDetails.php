<?php

require 'vendor/autoload.php';

use Services\BaseBarkaPayPaymentService;

try {
    // Instancie le service avec les clés API via .env
    $paymentService = new BaseBarkaPayPaymentService();

    // ID public du paiement que tu souhaites récupérer
    $publicId = 'iweidiewd743kwedk';

    // Appelle la méthode getPaymentDetails
    $response = $paymentService->getPaymentDetails($publicId);

    // Affiche le résultat
    print_r($response);

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
