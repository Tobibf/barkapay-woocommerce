<?php

/**
 * Plugin Name: BarkaPay for WooCommerce
 * Description: BarkaPay payment gateway for WooCommerce.
 * Version: 1.0.0
 * Author: Ulrich TRAORE
 * Text Domain: barkapay-woocommerce
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load BarkaPay SDK via Composer Autoload
if (file_exists(plugin_dir_path(__FILE__) . 'includes/barkapay-sdk/vendor/autoload.php')) {
    require_once plugin_dir_path(__FILE__) . 'includes/barkapay-sdk/vendor/autoload.php';
} else {
    add_action('admin_notices', function () {
        echo '<div class="error"><p><strong>BarkaPay for WooCommerce:</strong> Error: The SDK <code>vendor/autoload.php</code> file is missing. Run <code>composer install</code> in <code>includes/barkapay-sdk/</code>.</p></div>';
    });
    return;
}

// Ensure WooCommerce is active before including the gateway class
function barkapay_check_woocommerce()
{
    if (!class_exists('WC_Payment_Gateway')) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p><strong>BarkaPay for WooCommerce:</strong> WooCommerce must be activated for this plugin to work. Please activate WooCommerce before enabling this plugin.</p></div>';
        });
        return;
    }

    require_once plugin_dir_path(__FILE__) . 'includes/class-barkapay-gateway.php';
}
add_action('plugins_loaded', 'barkapay_check_woocommerce', 11);

// Register BarkaPay as a WooCommerce payment gateway
add_filter('woocommerce_payment_gateways', 'add_barkapay_gateway');
function add_barkapay_gateway($gateways)
{
    $gateways[] = 'WC_Gateway_BarkaPay';
    return $gateways;
}

// Add BarkaPay settings page
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

// Render settings page with proper styling
function barkapay_options_page()
{
    wp_enqueue_style('barkapay-admin-style', plugin_dir_url(__FILE__) . 'assets/barkapay.css');
?>
    <div class="wrap barkapay-settings-page">
        <h1>🔹 BarkaPay Settings</h1>
        <p>Configure your BarkaPay API credentials below:</p>

        <form method="post" action="options.php">
            <?php
            settings_fields('barkapay_options_group');
            do_settings_sections('barkapay');
            submit_button('Save BarkaPay Settings');
            ?>
        </form>
    </div>
<?php
}

// Initialize settings
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
    $options = get_option('barkapay_options', []);
?>
    <input type="text" name="barkapay_options[barkapay_api_key]" value="<?php echo esc_attr($options['barkapay_api_key'] ?? ''); ?>" />
<?php
}

function barkapay_api_secret_render()
{
    $options = get_option('barkapay_options', []);
?>
    <input type="password" name="barkapay_options[barkapay_api_secret]" value="<?php echo esc_attr($options['barkapay_api_secret'] ?? ''); ?>" />
<?php
}

function barkapay_sci_key_render()
{
    $options = get_option('barkapay_options', []);
?>
    <input type="text" name="barkapay_options[barkapay_sci_key]" value="<?php echo esc_attr($options['barkapay_sci_key'] ?? ''); ?>" />
<?php
}

function barkapay_sci_secret_render()
{
    $options = get_option('barkapay_options', []);
?>
    <input type="password" name="barkapay_options[barkapay_sci_secret]" value="<?php echo esc_attr($options['barkapay_sci_secret'] ?? ''); ?>" />
<?php
}
