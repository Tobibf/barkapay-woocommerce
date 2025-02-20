<?php

/**
 * Plugin Name: BarkaPay for WooCommerce
 * Description: BarkaPay payment gateway for WooCommerce.
 * Version: 1.0.0
 * Author: Ulrich TRAORE
 * Text Domain: barkapay-woocommerce
 */

// Security: Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Include the payment gateway class
require_once plugin_dir_path(__FILE__) . 'includes/class-wc-gateway-barkapay.php';

// Register BarkaPay as a WooCommerce payment gateway
add_filter('woocommerce_payment_gateways', 'add_barkapay_gateway');
function add_barkapay_gateway($gateways)
{
    $gateways[] = 'WC_Gateway_BarkaPay';
    return $gateways;
}

// Add an admin settings menu for BarkaPay
add_action('admin_menu', 'barkapay_add_admin_menu');
function barkapay_add_admin_menu()
{
    add_options_page(
        'BarkaPay Settings',
        'BarkaPay',
        'manage_options',
        'barkapay',
        'barkapay_options_page'
    );
}

// Render the settings page
function barkapay_options_page()
{
?>
    <div class="wrap">
        <h1>BarkaPay Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('barkapay_options_group');
            do_settings_sections('barkapay');
            submit_button();
            ?>
        </form>
    </div>
<?php
}

// Initialize plugin settings
add_action('admin_init', 'barkapay_settings_init');
function barkapay_settings_init()
{
    register_setting('barkapay_options_group', 'barkapay_options');

    add_settings_section(
        'barkapay_settings_section',
        'BarkaPay API Configuration',
        'barkapay_settings_section_callback',
        'barkapay'
    );

    // Define API settings fields
    add_settings_field('barkapay_api_key', 'API Key', 'barkapay_api_key_render', 'barkapay', 'barkapay_settings_section');
    add_settings_field('barkapay_api_secret', 'API Secret', 'barkapay_api_secret_render', 'barkapay', 'barkapay_settings_section');
    add_settings_field('barkapay_sci_key', 'SCI Key', 'barkapay_sci_key_render', 'barkapay', 'barkapay_settings_section');
    add_settings_field('barkapay_sci_secret', 'SCI Secret', 'barkapay_sci_secret_render', 'barkapay', 'barkapay_settings_section');
}

function barkapay_settings_section_callback()
{
    echo 'Enter your BarkaPay API credentials below:';
}

function barkapay_api_key_render()
{
    $options = get_option('barkapay_options');
?>
    <input type="text" name="barkapay_options[barkapay_api_key]" value="<?php echo isset($options['barkapay_api_key']) ? esc_attr($options['barkapay_api_key']) : ''; ?>" />
<?php
}

function barkapay_api_secret_render()
{
    $options = get_option('barkapay_options');
?>
    <input type="password" name="barkapay_options[barkapay_api_secret]" value="<?php echo isset($options['barkapay_api_secret']) ? esc_attr($options['barkapay_api_secret']) : ''; ?>" />
<?php
}

function barkapay_sci_key_render()
{
    $options = get_option('barkapay_options');
?>
    <input type="text" name="barkapay_options[barkapay_sci_key]" value="<?php echo isset($options['barkapay_sci_key']) ? esc_attr($options['barkapay_sci_key']) : ''; ?>" />
<?php
}

function barkapay_sci_secret_render()
{
    $options = get_option('barkapay_options');
?>
    <input type="password" name="barkapay_options[barkapay_sci_secret]" value="<?php echo isset($options['barkapay_sci_secret']) ? esc_attr($options['barkapay_sci_secret']) : ''; ?>" />
<?php
}
