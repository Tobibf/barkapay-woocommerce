# BarkaPay PHP SDK

Le **BarkaPay PHP SDK** permet aux marchands d'intégrer facilement les services de paiement de BarkaPay dans leurs applications, en offrant une large gamme d'options de paiements via l'API principale, ainsi que des options spécifiques pour des cas comme **Orange Money** et **Moov Money**.

## Installation

1. Assurez-vous d'avoir installé Composer. Si ce n’est pas encore fait, suivez les instructions [ici](https://getcomposer.org/).
2. Installez le SDK dans votre projet via Composer :

```sh
composer require barkapay-sa/barkapay-php-sdk
```

ou

```sh
composer require barkapay-sa/barkapay-php-sdk:dev-main
```

## Configuration

1. Créez un fichier `.env` à la racine de votre projet.
2. Ajoutez vos clés API dans le fichier `.env` :

```env
BKP_API_KEY=your_api_key
BKP_API_SECRET=your_api_secret
BKP_SCI_KEY=your_sci_key
BKP_SCI_SECRET=your_sci_secret
```

## Utilisation

### 1. Paiements via APIBarkaPayPaymentService

Le service `APIBarkaPayPaymentService` est le point d’entrée principal pour la plupart des types de paiements.

#### Exemple de paiement mobile standard :

```php
require 'vendor/autoload.php';
use Services\APIBarkaPayPaymentService;

$paymentService = new APIBarkaPayPaymentService();
$paymentDetails = [
    'sender_phonenumber' => '770000000',
    'amount' => 5000,
    'order_id' => 'ORDER123'
];

$response = $paymentService->createMobilePayment($paymentDetails);
print_r($response);
```

### 2. Paiements via SCIBarkaPayPaymentService

Le service `SCIBarkaPayPaymentService` est utilisé pour créer des liens de paiement et des intégrations spécifiques aux scénarios de **Service Client Interface**.

#### Exemple de création de lien de paiement :

```php
require 'vendor/autoload.php';
use Services\SCIBarkaPayPaymentService;

$sciPaymentService = new SCIBarkaPayPaymentService();
$paymentLinkData = [
    'amount' => 5000,
    'order_id' => 'ORDER123',
    'callback_url' => 'https://example.com/callback'
];

$response = $sciPaymentService->createPaymentLink($paymentLinkData);
print_r($response);
```

### 3. Paiement spécifique : Orange Money

Cas d'utilisation spécifique pour les paiements **Orange Money** au Burkina Faso :

```php
require 'vendor/autoload.php';
use Services\Specs\OrangeMoneyBFBarkaPayPaymentService;

$orangeMoneyService = new OrangeMoneyBFBarkaPayPaymentService();
$paymentDetails = [
    'sender_phonenumber' => '77921392',
    'amount' => 5000,
    'otp' => '123456',
    'order_id' => 'ORDER123',
];

$response = $orangeMoneyService->proceedPayment($paymentDetails);
print_r($response);
```

### 4. Paiement spécifique : Moov Money

Pour initier un paiement via **Moov Money** au Burkina Faso :

```php
require 'vendor/autoload.php';
use Services\Specs\MoovMoneyBFBarkaPayPaymentService;

$moovMoneyService = new MoovMoneyBFBarkaPayPaymentService();
$paymentDetails = [
    'sender_phonenumber' => '62002208',
    'amount' => 100,
    'order_id' => 'ORDER123',
    'callback_url' => 'https://example.com/callback'
];

$response = $moovMoneyService->initMobilePayment($paymentDetails);
print_r($response);
```

### 5. Vérification du paiement

Vérifiez l’état d’un paiement avec son ID public :

```php
$publicId = 'public_id_recu';
$verifyResponse = $moovMoneyService->verifyMobilePayment($publicId);
print_r($verifyResponse);
```

## Fonctions disponibles

- `createMobilePayment()` : Créer un paiement mobile standard via `APIBarkaPayPaymentService`.
- `createMobileTransfer()` : Créer un transfert mobile via `APIBarkaPayPaymentService`.
- `createPaymentLink()` : Créer un lien de paiement via `SCIBarkaPayPaymentService`.
- `proceedPayment()` : Effectuer un paiement **Orange Money**.
- `initMobilePayment()` : Initialiser un paiement **Moov Money**.
- `verifyMobilePayment()` : Vérifier l’état d’un paiement.
- `getPaymentDetails()` : Récupérer les détails d’un paiement spécifique.
- `getTransferDetails()` : Récupérer les détails d’un transfert spécifique.
- `verifyCredentials()` : Vérifie les informations d'authentification de l'API via `BaseBarkaPayPaymentService`.
- `getAvailableServices()` : Récupère les services disponibles via BarkaPay via `BaseBarkaPayPaymentService`.
- `getUserInfos()` : Récupère les informations de l'utilisateur via `BaseBarkaPayPaymentService`.
- `getAccountsBalances()` : Récupère les soldes des comptes associés via BarkaPay via `BaseBarkaPayPaymentService`.
- `getOperatorsInfos()` : Récupère les informations sur les opérateurs via `BaseBarkaPayPaymentService`.

## Débogage

Si les variables d'environnement ne sont pas chargées correctement, assurez-vous que le fichier `.env` est bien positionné et que la configuration est correcte :

```php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
```

## Assistance

Pour toute question ou problème, veuillez contacter le support technique via :
- 📧 [support@barkapay.com](mailto:support@barkapay.com)
- 🌐 [https://barkapay.com/help-center](https://barkapay.com/help-center)
