<?php
/**
 * Plugin Name: WooCommerce Local Pickup Store
 * Plugin URI: https://github.com/vadikonline1/woo-pickup-store
 * Description: Adds a "Personal Pickup" shipping method with store selection.
 * Version: 1.0.0
 * Author: Steel..xD
 * License: GPL v2 or later
 * Text Domain: woo-pickup-store
 * Requires Plugins: woocommerce, github-plugin-manager-main
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WCPS_VERSION', '1.0.0');
define('WCPS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCPS_PLUGIN_URL', plugin_dir_url(__FILE__));

function wcps_log($message) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[WC Pickup Store] ' . $message);
    }
}

// Check WooCommerce
function wcps_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p><strong>WC Pickup Store</strong> requires WooCommerce to be installed and activated.</p></div>';
        });
        return false;
    }
    return true;
}

// Initialize
add_action('plugins_loaded', 'wcps_init_plugin');
function wcps_init_plugin() {
    if (!wcps_check_woocommerce()) {
        return;
    }

    require_once WCPS_PLUGIN_DIR . 'includes/class-wc-store-cpt.php';
    require_once WCPS_PLUGIN_DIR . 'includes/class-wc-pickup-shipping-method.php';
    require_once WCPS_PLUGIN_DIR . 'includes/class-wc-pickup-settings.php';
    require_once WCPS_PLUGIN_DIR . 'includes/class-wc-pickup-checkout.php';

    new WC_Store_CPT();
    new WC_Pickup_Settings();
    new WC_Pickup_Checkout();
}

// Register shipping method
add_action('woocommerce_shipping_init', 'wcps_include_shipping_method');
function wcps_include_shipping_method() {
    if (!class_exists('WC_Pickup_Store_Shipping_Method')) {
        require_once WCPS_PLUGIN_DIR . 'includes/class-wc-pickup-shipping-method.php';
    }
}

add_filter('woocommerce_shipping_methods', 'wcps_add_shipping_method');
function wcps_add_shipping_method($methods) {
    $methods['pickup_store'] = 'WC_Pickup_Store_Shipping_Method';
    return $methods;
}

register_activation_hook(__FILE__, 'wcps_activate');
function wcps_activate() {
    require_once WCPS_PLUGIN_DIR . 'includes/class-wc-store-cpt.php';
    new WC_Store_CPT();
    flush_rewrite_rules();
    wcps_log('Plugin activated');
}


function enqueue_scripts() {
    if (is_checkout()) {
        wp_enqueue_style('wcps-style', WCPS_PLUGIN_URL . 'assets/css/pickup-style.css', array(), WCPS_VERSION);
        wp_enqueue_script('wcps-checkout', WCPS_PLUGIN_URL . 'assets/js/checkout-pickup.js', array('jquery'), WCPS_VERSION, true);
        
        // Localize script with labels
        wp_localize_script('wcps-checkout', 'wcps_labels', array(
            'address_label' => get_option('wcps_label_address', '📍 Address'),
            'hours_label' => get_option('wcps_label_hours', '🕒 Business Hours'),
            'phone_label' => get_option('wcps_label_phone', '📞 Phone'),
            'email_label' => get_option('wcps_label_email', '📧 Email'),
        ));
    }
}
