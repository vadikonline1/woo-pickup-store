<?php
/**
 * Plugin Name: Local Pickup Store for WooCommerce
 * Plugin URI: https://github.com/vadikonline1/wc-pickup-store
 * Description: Adds a "Personal Pickup" shipping method with store selection for WooCommerce.
 * Version: 1.0.0
 * Author: Steel..xD
 * License: GPL v2 or later
 * Text Domain: wc-pickup-store-main
 * Requires Plugins: woocommerce, github-plugin-manager
 * WC requires at least: 6.0
 * WC tested up to: 9.0
 */
 
if (!defined('ABSPATH')) {
    exit;
}

define('WCPS_VERSION', '1.0.0');
define('WCPS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCPS_PLUGIN_URL', plugin_dir_url(__FILE__));


// Check WooCommerce
function wcps_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wcps_woocommerce_missing_notice');
        return false;
    }
    return true;
}

function wcps_woocommerce_missing_notice() {
    echo '<div class="error"><p><strong>Local Pickup Store for WooCommerce</strong> requires WooCommerce to be installed and activated.</p></div>';
}

// Initialize plugin
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
    wcps_debug_log('Plugin activated');
}