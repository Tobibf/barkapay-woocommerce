<?php

require 'vendor/autoload.php';

use Services\BaseBarkaPayPaymentService;

$results = [];

try {
    // Instancie le service avec les clés API via .env
    $paymentService = new BaseBarkaPayPaymentService();

    // Vérification des informations d'authentification
    try {
        echo "Vérification des informations d'authentification :\n";
        $results['verifyCredentials'] = $paymentService->verifyCredentials();
        print_r($results['verifyCredentials']);
    } catch (Exception $e) {
        echo "Erreur lors de la vérification des informations d'authentification : " . $e->getMessage() . "\n";
        $results['verifyCredentials'] = "Erreur: " . $e->getMessage();
    }

    // Services disponibles
    try {
        echo "\nServices disponibles :\n";
        $results['getAvailableServices'] = $paymentService->getAvailableServices();
        print_r($results['getAvailableServices']);
    } catch (Exception $e) {
        echo "Erreur lors de la récupération des services disponibles : " . $e->getMessage() . "\n";
        $results['getAvailableServices'] = "Erreur: " . $e->getMessage();
    }

    // Informations de l'utilisateur
    try {
        echo "\nInformations de l'utilisateur :\n";
        $results['getUserInfos'] = $paymentService->getUserInfos();
        print_r($results['getUserInfos']);
    } catch (Exception $e) {
        echo "Erreur lors de la récupération des informations de l'utilisateur : " . $e->getMessage() . "\n";
        $results['getUserInfos'] = "Erreur: " . $e->getMessage();
    }

    // Soldes des comptes
    try {
        echo "\nSoldes des comptes :\n";
        $results['getAccountsBalances'] = $paymentService->getAccountsBalances();
        print_r($results['getAccountsBalances']);
    } catch (Exception $e) {
        echo "Erreur lors de la récupération des soldes des comptes : " . $e->getMessage() . "\n";
        $results['getAccountsBalances'] = "Erreur: " . $e->getMessage();
    }

    // Informations des opérateurs
    try {
        echo "\nInformations des opérateurs :\n";
        $results['getOperatorsInfos'] = $paymentService->getOperatorsInfos();
        print_r($results['getOperatorsInfos']);
    } catch (Exception $e) {
        echo "Erreur lors de la récupération des informations des opérateurs : " . $e->getMessage() . "\n";
        $results['getOperatorsInfos'] = "Erreur: " . $e->getMessage();
    }

} catch (Exception $e) {
    echo "Erreur lors de l'initialisation du service : " . $e->getMessage();
}

// Bilan complet des appels
echo "\n\nBilan complet des appels :\n";
print_r($results);
