<?php

namespace Services\Specs;

use Services\APIBarkaPayPaymentService;
use InvalidArgumentException;
use RuntimeException;
use Exception;

/**
 * Extends the APIBarkaPayPaymentService to offer mobile payment functionalities
 * for Moov Money users in Burkina Faso.
 */
class MoovMoneyBFBarkaPayPaymentService extends APIBarkaPayPaymentService
{
    // Country code for Burkina Faso.
    const COUNTRY = 'BFA';
    // Operator code for Moov Money.
    const OPERATOR = 'MOOV';

    /**
     * Initializes a mobile payment using Moov Money.
     *
     * @param array $paymentDetails The details of the payment to be made.
     * @param string $language Optional. The language preference for API responses.
     * @return mixed The response from the payment API.
     * @throws InvalidArgumentException If any required payment detail is missing.
     * @throws RuntimeException If an error occurs while sending the payment request.
     */
    public function initMobilePayment(array $paymentDetails, string $language = 'fr')
    {
        // List of required fields.
        $requiredFields = ['sender_phonenumber', 'amount', 'order_id', 'callback_url'];
        
        // Validate required fields.
        foreach ($requiredFields as $field) {
            if (empty($paymentDetails[$field])) {
                throw new InvalidArgumentException("Missing required field: $field.");
            }
        }

        // Prepare the payload.
        $payload = [
            'sender_country' => self::COUNTRY,
            'operator' => self::OPERATOR,
            'sender_phonenumber' => $paymentDetails['sender_phonenumber'],
            'amount' => $paymentDetails['amount'],
            'order_id' => $paymentDetails['order_id'],
            'order_id_unicity' => $paymentDetails['order_id_unicity'] ?? 0,
            'callback_url' => $paymentDetails['callback_url'],
            'order_data' => $paymentDetails['order_data'] ?? 'Transaction data',
            'otp' => null, // Optional OTP.
        ];

        // Attempt the request with retry logic.
        $attempt = 0;
        $maxAttempts = 2;

        while ($attempt < $maxAttempts) {
            try {
                // Send the payment request.
                return $this->createMobilePayment($payload, $language);
            } catch (Exception $e) {
                // Retry if the first attempt fails with a 500 error.
                if ($e->getCode() == 500 && $attempt == 0) {
                    $attempt++;
                    sleep(1); // Retry after 1 second.
                } else {
                    throw new RuntimeException("Moov Pay: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Verifies the status of a mobile payment.
     *
     * @param string $public_id The public identifier of the payment to verify.
     * @param string $language The language preference for API responses.
     * @return mixed The payment details from the API response.
     * @throws RuntimeException If an error occurs during the verification request.
     */
    public function verifyMobilePayment(string $public_id, string $language = 'fr')
    {
        try {
            return $this->getPaymentDetails($public_id, $language);
        } catch (Exception $e) {
            throw new RuntimeException("An error occurred while verifying the mobile payment: " . $e->getMessage());
        }
    }
}
