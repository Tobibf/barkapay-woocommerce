<?php

namespace Services;

use InvalidArgumentException;

class APIBarkaPayPaymentService extends BaseBarkaPayPaymentService
{
    public function createMobilePayment(array $paymentDetails, string $language = 'fr')
    {
        $url = self::BASE_URL . 'payment/mobile/api';
        $requiredFields = ['sender_country', 'sender_phonenumber', 'operator', 'amount', 'order_id'];

        foreach ($requiredFields as $field) {
            if (empty($paymentDetails[$field])) {
                throw new InvalidArgumentException("Missing required field: $field.");
            }
        }

        $payload = [
            'sender_country' => $paymentDetails['sender_country'],
            'operator' => $paymentDetails['operator'],
            'sender_phonenumber' => $paymentDetails['sender_phonenumber'],
            'otp' => $paymentDetails['otp'] ?? null, // OTP if required
            'set_amount' => 'fixed',
            'amount' => $paymentDetails['amount'],
            'order_id' => $paymentDetails['order_id'],
            'callback_url' => $paymentDetails['callback_url'] ?? 'N/A',
        ];

        return $this->sendHttpRequest(self::METHOD_POST, $url, ['Accept-Language' => $language], $payload);
    }

    public function createMobileTransfer(array $transferDetails, string $language = 'fr')
    {
        $url = self::BASE_URL . 'transfer/request/mobile';

        // Liste des champs requis
        $requiredFields = ['receiver_country', 'receiver_phonenumber', 'operator', 'amount', 'order_id', 'callback_url'];

        // Validation des champs requis
        foreach ($requiredFields as $field) {
            if (empty($transferDetails[$field])) {
                throw new InvalidArgumentException("Missing required field: $field.");
            }
        }

        // Construire le payload pour la requête
        $payload = [
            'receiver_country' => $transferDetails['receiver_country'],
            'receiver_phonenumber' => $transferDetails['receiver_phonenumber'],
            'operator' => $transferDetails['operator'],
            'amount' => $transferDetails['amount'],
            'note' => $transferDetails['note'] ?? null, // Optionnel
            'order_id' => $transferDetails['order_id'],
            'callback_url' => $transferDetails['callback_url'],
            'order_data' => $transferDetails['order_data'] ?? json_encode(['order_data' => 'no-data']),
            'ignore_double_spend_risk' => $transferDetails['ignore_double_spend_risk'] ?? '0',
        ];

        return $this->sendHttpRequest(self::METHOD_POST, $url, ['Accept-Language' => $language], $payload);
    }
}
