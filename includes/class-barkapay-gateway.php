<?php

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include the BarkaPay SDK (if required)
require_once plugin_dir_path(__FILE__) . '../barkapay-sdk/src/Services/BarkaPayClient.php';

class WC_Gateway_BarkaPay extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id                 = 'barkapay';
        $this->method_title       = 'BarkaPay';
        $this->method_description = 'Accept payments via BarkaPay.';
        $this->has_fields         = false;

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        // Define properties
        $this->title       = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->api_key     = $this->get_option('api_key');
        $this->api_secret  = $this->get_option('api_secret');
        $this->sci_key     = $this->get_option('sci_key');
        $this->sci_secret  = $this->get_option('sci_secret');

        // Handle BarkaPay callback automatically
        add_action('woocommerce_api_wc_gateway_barkapay', array($this, 'handle_barkapay_callback'));

        // Save settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * Initialize form fields for settings
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable BarkaPay',
                'default' => 'yes',
            ),
            'title' => array(
                'title'       => 'Title',
                'type'        => 'text',
                'default'     => 'BarkaPay',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => 'Description',
                'type'        => 'textarea',
                'default'     => 'Pay securely via BarkaPay.',
            ),
            'api_key' => array(
                'title' => 'API Key',
                'type'  => 'text',
            ),
            'api_secret' => array(
                'title' => 'API Secret',
                'type'  => 'password',
            ),
            'sci_key' => array(
                'title' => 'SCI Key',
                'type'  => 'text',
            ),
            'sci_secret' => array(
                'title' => 'SCI Secret',
                'type'  => 'password',
            ),
        );
    }

    /**
     * Process payment request and redirect to BarkaPay
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        // Initialize BarkaPay Client
        $barkapay = new \Services\BarkaPayClient(
            $this->api_key,
            $this->api_secret,
            $this->sci_key,
            $this->sci_secret
        );

        // Create payment request
        $payment_data = [
            'title'        => 'Order ' . $order->get_order_number(),
            'amount'       => $order->get_total(),
            'order_id'     => $order->get_order_number(),
            'callback_url' => home_url('/?wc-api=wc_gateway_barkapay'),
            'pay_url'      => $this->get_return_url($order),
            'no_pay_url'   => $order->get_cancel_order_url(),
        ];

        // Add customer email if available
        if ($order->get_billing_email()) {
            $payment_data['payer_notification_email'] = $order->get_billing_email();
            $payment_data['notify_payer'] = 1; // Notify customer via email
        }

        // Add additional order data if needed
        $order_data = [
            'customer_id' => $order->get_customer_id(),
            'items'       => []
        ];

        foreach ($order->get_items() as $item_id => $item) {
            $order_data['items'][] = [
                'product_id' => $item->get_product_id(),
                'quantity'   => $item->get_quantity(),
                'total'      => $item->get_total(),
            ];
        }

        // Add order data to payment data
        $payment_data['order_data'] = json_encode($order_data);

        try {
            $response = $barkapay->createPaymentLink($payment_data);

            if (isset($response->content['link'])) {
                $order->update_status('pending', 'Awaiting payment via BarkaPay.');
                wc_reduce_stock_levels($order_id);
                WC()->cart->empty_cart();

                return [
                    'result'   => 'success',
                    'redirect' => $response->content['link'],
                ];
            } else {
                wc_add_notice('Error: Payment link not received from BarkaPay.', 'error');
                return ['result' => 'failure'];
            }
        } catch (Exception $e) {
            wc_add_notice('BarkaPay error: ' . $e->getMessage(), 'error');
            return ['result' => 'failure'];
        }
    }

    /**
     * Handle the callback from BarkaPay
     */
    public function handle_barkapay_callback()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['order_id']) || !isset($data['status'])) {
            http_response_code(400);
            exit;
        }

        $order = wc_get_order((int) $data['order_id']);

        if ($order) {
            if ((int) $data['status'] === 1) {
                $order->payment_complete();
            } else {
                $order->update_status('failed');
            }
        }

        http_response_code(200);
        exit;
    }
}
