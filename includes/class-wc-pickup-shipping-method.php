<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Pickup_Store_Shipping_Method extends WC_Shipping_Method {

    public function __construct($instance_id = 0) {
        $this->id = 'pickup_store';
        $this->instance_id = absint($instance_id);
        $this->method_title = __('Personal Pickup', 'wc-pickup-store');
        $this->method_description = __('Customer picks up order from a selected store.', 'wc-pickup-store');
        $this->supports = array(
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
        );
        
        $this->init();
        
        $this->title = isset($this->settings['title']) && !empty($this->settings['title']) 
            ? $this->settings['title'] 
            : get_option('wcps_method_title', 'Personal Pickup');
    }

    public function init() {
        $this->init_form_fields();
        $this->init_settings();
        
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __('Enable', 'wc-pickup-store'),
                'type'        => 'checkbox',
                'label'       => __('Enable this shipping method', 'wc-pickup-store'),
                'default'     => get_option('wcps_enabled', 'yes'),
            ),
            'title' => array(
                'title'       => __('Method Title', 'wc-pickup-store'),
                'type'        => 'text',
                'description' => __('Name displayed at checkout.', 'wc-pickup-store'),
                'default'     => get_option('wcps_method_title', 'Personal Pickup'),
                'desc_tip'    => true,
            ),
            'cost' => array(
                'title'       => __('Shipping Cost', 'wc-pickup-store'),
                'type'        => 'price',
                'description' => __('Cost for pickup.', 'wc-pickup-store'),
                'default'     => get_option('wcps_shipping_cost', 0),
                'desc_tip'    => true,
            ),
        );
    }

    public function calculate_shipping($package = array()) {
        $cost = isset($this->settings['cost']) ? floatval($this->settings['cost']) : floatval(get_option('wcps_shipping_cost', 0));
        
        $rate = array(
            'id'      => $this->get_rate_id(),
            'label'   => $this->title,
            'cost'    => $cost,
            'package' => $package,
        );
        
        $this->add_rate($rate);
    }
}
