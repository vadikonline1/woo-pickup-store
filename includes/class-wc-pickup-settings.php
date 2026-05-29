<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Pickup_Settings {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Pickup Store Settings', 'wc-pickup-store-main'),
            __('Pickup Store', 'wc-pickup-store-main'),
            'manage_options',
            'wc-pickup-store-settings',
            array($this, 'settings_page')
        );
    }

    public function register_settings() {
        register_setting('wcps_settings_group', 'wcps_enabled', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('wcps_settings_group', 'wcps_method_title', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('wcps_settings_group', 'wcps_shipping_cost', array('sanitize_callback' => 'floatval'));
        register_setting('wcps_settings_group', 'wcps_pickup_notice', array('sanitize_callback' => 'wp_kses_post'));
        register_setting('wcps_settings_group', 'wcps_thankyou_title', array('sanitize_callback' => 'sanitize_text_field'));
        
        register_setting('wcps_settings_group', 'wcps_label_store', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('wcps_settings_group', 'wcps_label_address', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('wcps_settings_group', 'wcps_label_hours', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('wcps_settings_group', 'wcps_label_phone', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('wcps_settings_group', 'wcps_label_email', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('wcps_settings_group', 'wcps_select_placeholder', array('sanitize_callback' => 'sanitize_text_field'));
    }

    public function settings_page() {
        $enabled = get_option('wcps_enabled', 'yes');
        $method_title = get_option('wcps_method_title', 'Personal Pickup');
        $shipping_cost = get_option('wcps_shipping_cost', 0);
        $pickup_notice = get_option('wcps_pickup_notice', '📱 Please have your order number and ID with you.');
        $thankyou_title = get_option('wcps_thankyou_title', 'Pickup Order Information');
        
        $label_store = get_option('wcps_label_store', '🏪 Store');
        $label_address = get_option('wcps_label_address', '📍 Address');
        $label_hours = get_option('wcps_label_hours', '🕒 Business Hours');
        $label_phone = get_option('wcps_label_phone', '📞 Phone');
        $label_email = get_option('wcps_label_email', '📧 Email');
        $select_placeholder = get_option('wcps_select_placeholder', '— Select a store —');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Pickup Store Settings', 'wc-pickup-store-main'); ?></h1>
            
            <div class="notice notice-info">
                <p>
                    <strong><?php esc_html_e('Quick link:', 'wc-pickup-store-main'); ?></strong> 
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wc-settings&tab=shipping')); ?>" target="_blank">
                        <?php esc_html_e('WooCommerce → Settings → Shipping → Shipping Zones', 'wc-pickup-store-main'); ?>
                    </a>
                </p>
                <p>
                    📌 <strong><?php esc_html_e('Instructions:', 'wc-pickup-store-main'); ?></strong> 
                    <?php esc_html_e('After adding stores below, go to Shipping Zones, edit your zone and add the "Personal Pickup" shipping method.', 'wc-pickup-store-main'); ?>
                </p>
            </div>
            
            <form method="post" action="options.php">
                <?php settings_fields('wcps_settings_group'); ?>
                
                <h2><?php esc_html_e('General Settings', 'wc-pickup-store-main'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Module', 'wc-pickup-store-main'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wcps_enabled" value="yes" <?php checked($enabled, 'yes'); ?> />
                                <?php esc_html_e('Enable Personal Pickup shipping method', 'wc-pickup-store-main'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Shipping Method Name', 'wc-pickup-store-main'); ?></th>
                        <td>
                            <input type="text" name="wcps_method_title" value="<?php echo esc_attr($method_title); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Name displayed at checkout.', 'wc-pickup-store-main'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Shipping Cost (LEI)', 'wc-pickup-store-main'); ?></th>
                        <td>
                            <input type="number" step="0.01" name="wcps_shipping_cost" value="<?php echo esc_attr($shipping_cost); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Shipping cost. 0 = free.', 'wc-pickup-store-main'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">📋 <?php esc_html_e('Additional Notice', 'wc-pickup-store-main'); ?></th>
                        <td>
                            <textarea name="wcps_pickup_notice" rows="3" cols="50" class="regular-text"><?php echo esc_textarea($pickup_notice); ?></textarea>
                            <p class="description"><?php esc_html_e('Message shown at order completion.', 'wc-pickup-store-main'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">📦 <?php esc_html_e('Thank You Page Title', 'wc-pickup-store-main'); ?></th>
                        <td>
                            <input type="text" name="wcps_thankyou_title" value="<?php echo esc_attr($thankyou_title); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Title displayed on the Thank You page for pickup information.', 'wc-pickup-store-main'); ?></p>
                        </td>
                    </tr>
                </table>
                 
                <h2><?php esc_html_e('Customizable Field Labels', 'wc-pickup-store-main'); ?></h2>
                <p><?php esc_html_e('Change how the store details appear on Thank You page and emails:', 'wc-pickup-store-main'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row">🏪 <?php esc_html_e('Store Label', 'wc-pickup-store-main'); ?></th>
                        <td>
                            <input type="text" name="wcps_label_store" value="<?php echo esc_attr($label_store); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Address Label', 'wc-pickup-store-main'); ?></th>
                        <td>
                            <input type="text" name="wcps_label_address" value="<?php echo esc_attr($label_address); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Business Hours Label', 'wc-pickup-store-main'); ?></th>
                        <td>
                            <input type="text" name="wcps_label_hours" value="<?php echo esc_attr($label_hours); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Phone Label', 'wc-pickup-store-main'); ?></th>
                        <td>
                            <input type="text" name="wcps_label_phone" value="<?php echo esc_attr($label_phone); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Email Label', 'wc-pickup-store-main'); ?></th>
                        <td>
                            <input type="text" name="wcps_label_email" value="<?php echo esc_attr($label_email); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Select Placeholder', 'wc-pickup-store-main'); ?></th>
                        <td>
                            <input type="text" name="wcps_select_placeholder" value="<?php echo esc_attr($select_placeholder); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Save Settings', 'wc-pickup-store-main')); ?>
            </form>
            
            <hr>
            
            <h2><?php esc_html_e('Manage Stores', 'wc-pickup-store-main'); ?></h2>
            <p>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=wc_pickup_store')); ?>" class="button button-primary">
                    ➕ <?php esc_html_e('Add New Store', 'wc-pickup-store-main'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=wc_pickup_store')); ?>" class="button">
                    📋 <?php esc_html_e('Edit Stores', 'wc-pickup-store-main'); ?>
                </a>
            </p>
            
            <hr>
            
            <h2><?php esc_html_e('How to Add the Shipping Method', 'wc-pickup-store-main'); ?></h2>
            <ol>
                <li><?php esc_html_e('Go to WooCommerce → Settings → Shipping → Shipping Zones', 'wc-pickup-store-main'); ?></li>
                <li><?php esc_html_e('Edit your zone (e.g., Moldova)', 'wc-pickup-store-main'); ?></li>
                <li><?php esc_html_e('Click "Add shipping method"', 'wc-pickup-store-main'); ?></li>
                <li><?php esc_html_e('Select "Personal Pickup" from the list', 'wc-pickup-store-main'); ?></li>
                <li><?php esc_html_e('Click "Add shipping method"', 'wc-pickup-store-main'); ?></li>
                <li><?php esc_html_e('Click "Save changes"', 'wc-pickup-store-main'); ?></li>
            </ol>
            
            <div class="notice notice-success">
                <p>✅ <?php esc_html_e('Result: At checkout, when customers select "Personal Pickup", they can choose a store from your list.', 'wc-pickup-store-main'); ?></p>
            </div>
        </div>
        <?php
    }
}