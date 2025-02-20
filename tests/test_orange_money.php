<?php

require 'vendor/autoload.php';

use Services\Specs\OrangeMoneyBFBarkaPayPaymentService;

try {
    // Instancie le service de paiement Orange Money pour Burkina Faso
    $orangeMoneyService = new OrangeMoneyBFBarkaPayPaymentService();

    // Détails de paiement
    $paymentDetails = [
        'sender_phonenumber' => '77921392',
        'amount' => 100,
        'otp' => '123456',
        'order_id' => uniqid()
    ];

    // Appelle la méthode proceedPayment et affiche la réponse
    $response = $orangeMoneyService->proceedPayment($paymentDetails);

    // Affiche la réponse
    print_r($response);

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
