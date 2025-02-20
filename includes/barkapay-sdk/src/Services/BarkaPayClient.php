<?php

namespace Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use RuntimeException;
use Exception;

/**
 * Class BarkaPayClient
 *
 * Handles payment transactions using the BarkaPay API.
 */
class BarkaPayClient
{
    private string $apiKey;
    private string $apiSecret;
    private string $sciKey;
    private string $sciSecret;

    private const BASE_URL = 'https://api.barkapay.com/api/client/';
    private const CURRENCY = 'xof';

    private const METHOD_GET = 'GET';
    private const METHOD_POST = 'POST';

    /**
     * Constructor: Initializes the API keys.
     *
     * @param string $apiKey Public API key.
     * @param string $apiSecret Secret API key.
     * @param string $sciKey Public SCI key.
     * @param string $sciSecret Secret SCI key.
     *
     * @throws InvalidArgumentException If any API key is missing.
     */
    public function __construct(string $apiKey, string $apiSecret, string $sciKey, string $sciSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->sciKey = $sciKey;
        $this->sciSecret = $sciSecret;

        if (empty($this->apiKey) || empty($this->apiSecret) || empty($this->sciKey) || empty($this->sciSecret)) {
            throw new InvalidArgumentException("All API and SCI keys are required.");
        }
    }

    /**
     * Sends a generic HTTP request.
     *
     * @param string $method HTTP method (GET, POST, etc.).
     * @param string $url The endpoint URL.
     * @param array $headers Additional headers (default empty).
     * @param array $body Request body parameters (default empty).
     * @param string $keyType Specifies whether to use API or SCI key ('api' or 'sci').
     *
     * @return object Response containing statusCode and content.
     *
     * @throws RuntimeException If the request fails.
     */
    private function sendRequest(string $method, string $url, array $headers = [], array $body = [], string $keyType = 'api'): object
    {
        $client = new Client();

        // Select the appropriate API key and secret
        $apiKey = ($keyType === 'api') ? $this->apiKey : $this->sciKey;
        $apiSecret = ($keyType === 'api') ? $this->apiSecret : $this->sciSecret;

        // Merge authentication headers
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
            return (object) [
                'statusCode' => $response->getStatusCode(),
                'content' => json_decode($response->getBody()->getContents(), true),
            ];
        } catch (Exception $e) {
            throw new RuntimeException("Request failed: " . $e->getMessage());
        }
    }

    /**
     * Sends an HTTP request using the standard API key.
     *
     * @param string $method HTTP method.
     * @param string $url Endpoint URL.
     * @param array $headers Optional request headers.
     * @param array $body Optional request body.
     *
     * @return object Response object.
     */
    protected function sendHttpRequest(string $method, string $url, array $headers = [], array $body = []): object
    {
        return $this->sendRequest($method, $url, $headers, $body, 'api');
    }

    /**
     * Sends an HTTP request using the SCI key.
     *
     * @param string $method HTTP method.
     * @param string $url Endpoint URL.
     * @param array $headers Optional request headers.
     * @param array $body Optional request body.
     *
     * @return object Response object.
     */
    protected function sendSCIHttpRequest(string $method, string $url, array $headers = [], array $body = []): object
    {
        return $this->sendRequest($method, $url, $headers, $body, 'sci');
    }

    /**
     * Verifies API credentials.
     *
     * @return object API response.
     */
    public function verifyCredentials(): object
    {
        return $this->sendHttpRequest(self::METHOD_GET, self::BASE_URL . 'ping');
    }

    /**
     * Retrieves available services.
     *
     * @return object API response.
     */
    public function getAvailableServices(): object
    {
        return $this->sendHttpRequest(self::METHOD_GET, self::BASE_URL . 'status');
    }

    /**
     * Retrieves user information.
     *
     * @return object API response.
     */
    public function getUserInfos(): object
    {
        return $this->sendHttpRequest(self::METHOD_GET, self::BASE_URL . 'user');
    }

    /**
     * Retrieves account balances.
     *
     * @return object API response.
     */
    public function getAccountsBalances(): object
    {
        return $this->sendHttpRequest(self::METHOD_GET, self::BASE_URL . 'accounts');
    }

    /**
     * Retrieves information about available operators.
     *
     * @return object API response.
     */
    public function getOperatorsInfos(): object
    {
        return $this->sendHttpRequest(self::METHOD_GET, self::BASE_URL . 'operators-infos');
    }

    /**
     * Retrieves payment details using a public ID.
     *
     * @param string $publicId The public ID of the payment.
     * @param string $language Preferred response language ('fr', 'en').
     *
     * @return array Payment details.
     *
     * @throws InvalidArgumentException If the public ID is missing.
     * @throws RuntimeException If the request fails.
     */
    public function getPaymentDetails(string $publicId, string $language = 'fr'): array
    {
        if (empty($publicId)) {
            throw new InvalidArgumentException("The public ID must be provided.");
        }

        $url = self::BASE_URL . "payment/$publicId";
        $headers = ['Accept-Language' => $language];

        $response = $this->sendHttpRequest(self::METHOD_GET, $url, $headers);

        if ($response->statusCode !== 200) {
            throw new RuntimeException("Error retrieving payment details: HTTP {$response->statusCode} - " . ($response->content['message'] ?? 'Unknown error'));
        }

        return $response->content;
    }

    /**
     * Creates a payment link for mobile payments via SCI.
     *
     * @param array $paymentData Payment details including:
     *                           - 'title' (string): Payment title.
     *                           - 'amount' (float|string): Fixed payment amount.
     *                           - 'order_id' (string): Unique order ID.
     *                           - 'callback_url' (string): Callback URL on success.
     *                           - 'pay_url' (string): Redirection URL after success.
     *                           - 'no_pay_url' (string): Redirection URL after failure.
     * @param string $language Preferred language for response ('fr' default).
     *
     * @return object API response containing payment link details.
     *
     * @throws InvalidArgumentException If required fields are missing.
     */
    public function createPaymentLink(array $paymentData, string $language = 'fr'): object
    {
        $requiredFields = ['title', 'amount', 'order_id', 'callback_url'];
        foreach ($requiredFields as $field) {
            if (empty($paymentData[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

         if (!empty($paymentData['notify_payer']) && $paymentData['notify_payer'] == 1) {
            if (empty($paymentData['payer_notification_email'])) {
                throw new InvalidArgumentException("payer_notification_email is required when notify_payer is activated.");
            }
        }

        $url = self::BASE_URL . 'payment/mobile/web/create';

        // Set fixed amount type and default period
        $paymentData['set_amount'] = 'fixed';
        $paymentData['period'] = $paymentData['period'] ?? 1500;

        return $this->sendSCIHttpRequest(self::METHOD_POST, $url, ['Accept-Language' => $language], $paymentData);
    }
}
