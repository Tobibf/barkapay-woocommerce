<?php

namespace Services\Specs;

use Services\APIBarkaPayPaymentService;
use InvalidArgumentException;

class OrangeMoneyBFBarkaPayPaymentService extends APIBarkaPayPaymentService
{
    private $apiService;

    const COUNTRY = 'BFA'; // Burkina Faso
    const OPERATOR = 'ORANGE';

    /**
     * Initializes a mobile payment using Orange Money.
     *  * @return mixed The payment details from the API response.
     */
    public function proceedPayment(array $paymentDetails, string $language = 'fr')
    {
        // Required fields for payment
        $requiredFields = ['sender_phonenumber', 'amount', 'otp'];
        foreach ($requiredFields as $field) {
            if (empty($paymentDetails[$field])) {
                throw new InvalidArgumentException("Missing required field: $field.");
            }
        }

        // Prepare payload
        $payload = [
            'sender_country' => self::COUNTRY,
            'operator' => self::OPERATOR,
            'sender_phonenumber' => $paymentDetails['sender_phonenumber'],
            'amount' => $paymentDetails['amount'],
            'otp' => $paymentDetails['otp'],
            'order_id' => $paymentDetails['order_id'], // Unique Order ID
            'order_id_unicity' => 0,
            'order_data' => '',
        ];

        // Call the API service to make the payment
        return $this->createMobilePayment($payload, $language);
    }
}
