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
            __('Pickup-Store Settings', 'wc-pickup-store'),
            __('Pickup-Store Settings', 'wc-pickup-store'),
            'manage_options',
            'wc-pickup-store-settings',
            array($this, 'settings_page')
        );
    }

    public function register_settings() {
        register_setting('wcps_settings_group', 'wcps_enabled');
        register_setting('wcps_settings_group', 'wcps_method_title');
        register_setting('wcps_settings_group', 'wcps_shipping_cost');
        register_setting('wcps_settings_group', 'wcps_pickup_notice');
        register_setting('wcps_settings_group', 'wcps_thankyou_title');
        
        // Customizable field labels
        register_setting('wcps_settings_group', 'wcps_label_store');
        register_setting('wcps_settings_group', 'wcps_label_address');
        register_setting('wcps_settings_group', 'wcps_label_hours');
        register_setting('wcps_settings_group', 'wcps_label_phone');
        register_setting('wcps_settings_group', 'wcps_label_email');
        register_setting('wcps_settings_group', 'wcps_select_placeholder');
    }

    public function settings_page() {
        $enabled = get_option('wcps_enabled', 'yes');
        $method_title = get_option('wcps_method_title', 'Personal Pickup');
        $shipping_cost = get_option('wcps_shipping_cost', 0);
        $pickup_notice = get_option('wcps_pickup_notice', '📱 Please have your order number and ID with you.');
        $thankyou_title = get_option('wcps_thankyou_title', 'Pickup Order Information');
        
        // Customizable labels (with defaults)
        $label_store = get_option('wcps_label_store', '🏪 Store');
        $label_address = get_option('wcps_label_address', '📍 Address');
        $label_hours = get_option('wcps_label_hours', '🕒 Business Hours');
        $label_phone = get_option('wcps_label_phone', '📞 Phone');
        $label_email = get_option('wcps_label_email', '📧 Email');
        $select_placeholder = get_option('wcps_select_placeholder', '— Select a store —');
        ?>
        <div class="wrap">
            <h1><?php _e('Pickup Store Settings', 'wc-pickup-store'); ?></h1>
            
            <div class="notice notice-info">
                <p>
                    <strong>🔗 Quick link:</strong> 
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=shipping'); ?>" target="_blank">
                        WooCommerce → Settings → Shipping → Shipping Zones
                    </a>
                </p>
                <p>
                    📌 <strong>Instructions:</strong> After adding stores below, go to <strong>Shipping Zones</strong>, 
                    edit your zone and add the <strong>"Personal Pickup"</strong> shipping method.
                </p>
            </div>
            
            <form method="post" action="options.php">
                <?php settings_fields('wcps_settings_group'); ?>
                
                <h2><?php _e('General Settings', 'wc-pickup-store'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Module', 'wc-pickup-store'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wcps_enabled" value="yes" <?php checked($enabled, 'yes'); ?> />
                                <?php _e('Enable Personal Pickup shipping method', 'wc-pickup-store'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Shipping Method Name', 'wc-pickup-store'); ?></th>
                        <td>
                            <input type="text" name="wcps_method_title" value="<?php echo esc_attr($method_title); ?>" class="regular-text" />
                            <p class="description"><?php _e('Name displayed at checkout.', 'wc-pickup-store'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Shipping Cost (LEI)', 'wc-pickup-store'); ?></th>
                        <td>
                            <input type="number" step="0.01" name="wcps_shipping_cost" value="<?php echo esc_attr($shipping_cost); ?>" class="regular-text" />
                            <p class="description"><?php _e('Shipping cost. 0 = free.', 'wc-pickup-store'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">📋 <?php _e('Additional Notice', 'wc-pickup-store'); ?></th>
                        <td>
                            <textarea name="wcps_pickup_notice" rows="3" cols="50" class="regular-text"><?php echo esc_textarea($pickup_notice); ?></textarea>
                            <p class="description"><?php _e('Message shown at order completion.', 'wc-pickup-store'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">📦 <?php _e('Thank You Page Title', 'wc-pickup-store'); ?></th>
                        <td>
                            <input type="text" name="wcps_thankyou_title" value="<?php echo esc_attr($thankyou_title); ?>" class="regular-text" />
                            <p class="description"><?php _e('Title displayed on the Thank You page for pickup information.', 'wc-pickup-store'); ?></p>
                        </td>
                    </tr>
                </table>
                 
                <h2><?php _e('Customizable Field Labels', 'wc-pickup-store'); ?></h2>
                <p><?php _e('Change how the store details appear on Thank You page and emails:', 'wc-pickup-store'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row">🏪 <?php _e('Store Label', 'wc-pickup-store'); ?></th>
                        <td>
                            <input type="text" name="wcps_label_store" value="<?php echo esc_attr($label_store); ?>" class="regular-text" />
                            <p class="description"><?php _e('Label for store field (e.g., "🏪 Store")', 'wc-pickup-store'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Address Label', 'wc-pickup-store'); ?></th>
                        <td>
                            <input type="text" name="wcps_label_address" value="<?php echo esc_attr($label_address); ?>" class="regular-text" />
                            <p class="description"><?php _e('Label for address field (e.g., "📍 Address")', 'wc-pickup-store'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Business Hours Label', 'wc-pickup-store'); ?></th>
                        <td>
                            <input type="text" name="wcps_label_hours" value="<?php echo esc_attr($label_hours); ?>" class="regular-text" />
                            <p class="description"><?php _e('Label for hours field (e.g., "🕒 Business Hours")', 'wc-pickup-store'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Phone Label', 'wc-pickup-store'); ?></th>
                        <td>
                            <input type="text" name="wcps_label_phone" value="<?php echo esc_attr($label_phone); ?>" class="regular-text" />
                            <p class="description"><?php _e('Label for phone field (e.g., "📞 Phone")', 'wc-pickup-store'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Email Label', 'wc-pickup-store'); ?></th>
                        <td>
                            <input type="text" name="wcps_label_email" value="<?php echo esc_attr($label_email); ?>" class="regular-text" />
                            <p class="description"><?php _e('Label for email field (e.g., "📧 Email")', 'wc-pickup-store'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Select Placeholder', 'wc-pickup-store'); ?></th>
                        <td>
                            <input type="text" name="wcps_select_placeholder" value="<?php echo esc_attr($select_placeholder); ?>" class="regular-text" />
                            <p class="description"><?php _e('Text shown in dropdown before selection (e.g., "— Select a store —")', 'wc-pickup-store'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Save Settings', 'wc-pickup-store')); ?>
            </form>
            
            <hr>
            
            <h2>🏪 <?php _e('Manage Stores', 'wc-pickup-store'); ?></h2>
            <p>
                <a href="<?php echo admin_url('post-new.php?post_type=wc_pickup_store'); ?>" class="button button-primary">
                    ➕ <?php _e('Add New Store', 'wc-pickup-store'); ?>
                </a>
                <a href="<?php echo admin_url('edit.php?post_type=wc_pickup_store'); ?>" class="button">
                    📋 <?php _e('Edit Stores', 'wc-pickup-store'); ?>
                </a>
            </p>
            
            <hr>
            
            <h2><?php _e('How to Add the Shipping Method', 'wc-pickup-store'); ?></h2>
            <ol>
                <li>Go to <strong><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=shipping'); ?>" target="_blank">WooCommerce → Settings → Shipping → Shipping Zones</a></strong></li>
                <li>Edit your zone (e.g., <strong>Moldova</strong>)</li>
                <li>Click <strong>"Add shipping method"</strong></li>
                <li>Select <strong>"Personal Pickup"</strong> from the list</li>
                <li>Click <strong>"Add shipping method"</strong></li>
                <li>Click <strong>"Save changes"</strong></li>
            </ol>
            
            <div class="notice notice-success">
                <p>✅ <strong>Result:</strong> At checkout, when customers select "Personal Pickup", they can choose a store from your list.</p>
            </div>
        </div>
        
        <style>
            .form-table th { width: 260px; }
            .notice a { text-decoration: none; font-weight: bold; }
        </style>
        <?php
    }
}
