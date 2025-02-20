<?php

namespace Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use RuntimeException;
use Exception;
use Dotenv\Dotenv;

/**
 * Handles payment transactions with the BarkaPay payment service.
 */
class BaseBarkaPayPaymentService
{
    private $apiKey;
    private $apiSecret;
    private $sciKey;
    private $sciSecret;

    public const BASE_URL = 'https://api.barkapay.com/api/client/';
    const CURRENCY = 'xof';

    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

    /**
     * Constructor: Initializes API keys from environment variables.
     */
    public function __construct()
    {
        // Load environment variables from .env
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();

        $this->apiKey = $_ENV['BKP_API_KEY'] ?? null;
        $this->apiSecret = $_ENV['BKP_API_SECRET'] ?? null;
        $this->sciKey = $_ENV['BKP_SCI_KEY'] ?? null;
        $this->sciSecret = $_ENV['BKP_SCI_SECRET'] ?? null;

        if (empty($this->apiKey) || empty($this->apiSecret) || empty($this->sciKey) || empty($this->sciSecret)) {
            throw new InvalidArgumentException("API keys and SCI keys are required.");
        }
    }

    /**
     * Envoie les requêtes HTTP génériques.
     */
    private function sendRequest(string $method, string $url, array $headers = [], array $body = [], string $key = 'api')
    {
        $client = new Client();

        // Sélectionner la bonne clé API (API ou SCI)
        $apiKey = $key === 'api' ? $this->apiKey : $this->sciKey;
        $apiSecret = $key === 'api' ? $this->apiSecret : $this->sciSecret;

        $headers = array_merge([
            'X-Api-Key' => $apiKey,
            'X-Api-Secret' => $apiSecret,
            'Accept' => 'application/json',
        ], $headers);

        $options = ['headers' => $headers];
        if (!empty($body)) {
            $options['json'] = $body;
        }

        try {
            $response = $client->request($method, $url, $options);
            $contents = $response->getBody()->getContents();
            $decodedContents = json_decode($contents, true);
            $statusCode = $response->getStatusCode();

            return (object)['statusCode' => $statusCode, 'content' => $decodedContents];
        } catch (Exception $e) {
            throw new RuntimeException("Request failed: " . $e->getMessage());
        }
    }

    /**
     * Envoie une requête HTTP en utilisant l'API clé classique.
     */
    protected function sendHttpRequest(string $method, string $url, array $headers = [], array $body = [])
    {
        return $this->sendRequest($method, $url, $headers, $body, 'api');
    }

    /**
     * Envoie une requête HTTP en utilisant la clé SCI.
     */
    protected function sendSCIHttpRequest(string $method, string $url, array $headers = [], array $body = [])
    {
        return $this->sendRequest($method, $url, $headers, $body, 'sci');
    }

    public function verifyCredentials()
    {
        return $this->sendHttpRequest(self::METHOD_GET, self::BASE_URL . 'ping');
    }

    public function getAvailableServices()
    {
        return $this->sendHttpRequest(self::METHOD_GET, self::BASE_URL . 'status');
    }

    public function getUserInfos()
    {
        return $this->sendHttpRequest(self::METHOD_GET, self::BASE_URL . 'user');
    }

    public function getAccountsBalances()
    {
        return $this->sendHttpRequest(self::METHOD_GET, self::BASE_URL . 'accounts');
    }

    public function getOperatorsInfos()
    {
        return $this->sendHttpRequest(self::METHOD_GET, self::BASE_URL . 'operators-infos');
    }

    /**
     * Retrieves detailed information about a specific payment using its public ID.
     *
     * @param string $publicId The public identifier of the payment to retrieve.
     * @param string $language Preferred language for the API response ('fr', 'en').
     * @return object The details of the payment.
     * @throws InvalidArgumentException If the public ID is not provided.
     * @throws RuntimeException If the HTTP request fails.
     */
    public function getPaymentDetails(string $publicId, string $language = 'fr')
    {
        if (empty($publicId)) {
            throw new InvalidArgumentException("The public ID must be provided.");
        }

        try {
            $url = self::BASE_URL . "payment/$publicId";
            $headers = ['Accept-Language' => $language];

            // Envoi de la requête
            $response = $this->sendHttpRequest(self::METHOD_GET, $url, $headers);

            // Vérifie si le statut HTTP est 200 (succès)
            if ($response->statusCode !== 200) {
                $errorMessage = $response->content['message'] ?? "Unknown error";
                throw new RuntimeException("Error retrieving payment details: HTTP status {$response->statusCode} - $errorMessage");
            }

            // Retourne la réponse (contenu de la requête)
            return $response->content;
        } catch (Exception $e) {
            throw new RuntimeException("Error retrieving payment details: " . $e->getMessage());
        }
    }

    /**
     * Retrieves detailed information about a specific transfer using its public ID.
     *
     * @param string $publicId The public identifier of the transfer to retrieve.
     * @param string $language Optional. The preferred language for the API response. Defaults to 'fr' (French).
     * @return array The details of the transfer.
     * @throws InvalidArgumentException If the public ID is not provided.
     * @throws RuntimeException If the HTTP request fails.
     */
    public function getTransferDetails(string $publicId, string $language = 'fr')
    {
        if (empty($publicId)) {
            throw new InvalidArgumentException("The public ID must be provided.");
        }

        try {
            $url = self::BASE_URL . "transfer/$publicId";
            $headers = ['Accept-Language' => $language];

            $response = $this->sendHttpRequest(self::METHOD_GET, $url, $headers);

            // Vérifie si le statut HTTP est 200 (succès)
            if ($response->statusCode !== 200) {
                $errorMessage = $response->content['message'] ?? "Unknown error";
                throw new RuntimeException("Error retrieving payment details: HTTP status {$response->statusCode} - $errorMessage");
            }

            // Retourne la réponse (contenu de la requête)
            return $response->content;
        } catch (Exception $e) {
            throw new RuntimeException("Error retrieving transfer details: " . $e->getMessage());
        }
    }
}
