<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Pickup_Checkout {

    public function __construct() {
        $enabled = get_option('wcps_enabled', 'yes');
        
        if ($enabled !== 'yes') {
            return;
        }
        
        add_action('woocommerce_after_checkout_form', array($this, 'add_store_selector'), 20);
        add_action('woocommerce_checkout_process', array($this, 'validate_store_selection'));
        add_action('woocommerce_checkout_create_order', array($this, 'save_selected_store'), 10, 2);
        
        // Frontend display - thank you page (cu detalii complete)
        add_action('woocommerce_thankyou', array($this, 'display_pickup_info'), 5);
        
        // Email display (cu detalii complete)
        add_action('woocommerce_email_after_order_table', array($this, 'display_pickup_info_email'), 10, 4);
        
        // Scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('woocommerce_checkout_update_order_review', array($this, 'update_session_store'));
        
        // Admin order display - doar numele magazinului
        add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'display_pickup_admin_simple'));
        
        // Orders list column
        add_filter('manage_edit-shop_order_columns', array($this, 'add_pickup_store_column'), 20);
        add_action('manage_shop_order_posts_custom_column', array($this, 'display_pickup_store_column'), 20, 2);
    }
    
    public function add_store_selector() {
        $stores = WC_Store_CPT::get_stores();
        
        if (empty($stores)) {
            return;
        }
        
        $chosen_store = WC()->session->get('chosen_pickup_store');
        $placeholder = get_option('wcps_select_placeholder', '— Select a store —');
        
        $label_address = get_option('wcps_label_address', '📍 Address');
        $label_hours = get_option('wcps_label_hours', '🕒 Business Hours');
        $label_phone = get_option('wcps_label_phone', '📞 Phone');
        $label_email = get_option('wcps_label_email', '📧 Email');
        
        $chosen_methods = WC()->session->get('chosen_shipping_methods');
        $chosen_method = is_array($chosen_methods) ? $chosen_methods[0] : $chosen_methods;
        $is_pickup_initial = (strpos($chosen_method, 'pickup_store') !== false);
        $initial_display = $is_pickup_initial ? 'block' : 'none';
        ?>
        
        <div id="wcps-store-wrapper" class="wcps-store-wrapper" style="display: <?php echo $initial_display; ?>;">
            <div class="wcps-store-container">
                <label for="pickup_store_id" class="wcps-store-label">
                    🏪 <?php _e('Select pickup store:', 'wc-pickup-store'); ?> <span style="color: #a00;">*</span>
                </label>
                
                <select name="pickup_store_id" id="pickup_store_id" class="wcps-store-select">
                    <option value=""><?php echo esc_html($placeholder); ?></option>
                    <?php foreach ($stores as $store): ?>
                        <option value="<?php echo esc_attr($store['id']); ?>" 
                                data-address="<?php echo esc_attr($store['address']); ?>"
                                data-hours="<?php echo esc_attr($store['hours']); ?>"
                                data-phone="<?php echo esc_attr($store['phone']); ?>"
                                data-email="<?php echo esc_attr($store['email']); ?>"
                                <?php selected($chosen_store, $store['id']); ?>>
                            <?php echo esc_html($store['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <div id="wcps-store-details" class="wcps-store-details"></div>
            </div>
        </div>
        
        <style>
            .wcps-store-wrapper {
                margin: 20px 0;
                width: 100%;
                clear: both;
            }
            .wcps-store-container {
                background: #ffffff;
                border: 1px solid #e0e0e0;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }
            .wcps-store-label {
                display: block;
                font-weight: 600;
                font-size: 16px;
                margin-bottom: 12px;
                color: #222;
            }
            .wcps-store-select {
                width: 100%;
                min-height: 52px;
                padding: 14px 16px;
                border: 1px solid #dcdcdc;
                border-radius: 10px;
                background: #fff;
                font-size: 16px;
                cursor: pointer;
            }
            .wcps-store-select:focus {
                border-color: #7ad03a;
                outline: none;
                box-shadow: 0 0 0 3px rgba(122, 208, 58, 0.15);
            }
            .wcps-store-details {
                margin-top: 16px;
                padding: 16px;
                background: #f8f9fa;
                border-radius: 10px;
                border: 1px solid #e9ecef;
                font-size: 14px;
                line-height: 1.6;
                display: none;
            }
            .wcps-store-details p {
                margin: 8px 0;
            }
            .wcps-store-details strong {
                font-weight: 600;
                color: #111;
            }
            @media (max-width: 768px) {
                .wcps-store-container { padding: 16px; }
                .wcps-store-select { min-height: 56px; padding: 16px; font-size: 16px; }
                .wcps-store-details { padding: 14px; }
            }
        </style>
        
        <script type="text/javascript">
            (function($) {
                var labelAddress = <?php echo json_encode($label_address); ?>;
                var labelHours   = <?php echo json_encode($label_hours); ?>;
                var labelPhone   = <?php echo json_encode($label_phone); ?>;
                var labelEmail   = <?php echo json_encode($label_email); ?>;
                var selectorMoved = false;
            
                function displayStoreDetails() {
                    var $select = $('#pickup_store_id');
                    var storeId = $select.val();
            
                    if (!storeId) {
                        $('#wcps-store-details').stop(true, true).slideUp(200).html('');
                        return;
                    }
            
                    var selectedOption = $select.find('option:selected');
                    var address = selectedOption.data('address');
                    var hours   = selectedOption.data('hours');
                    var phone   = selectedOption.data('phone');
                    var email   = selectedOption.data('email');
            
                    var html = '';
                    if (address) html += '<p><strong>' + labelAddress + ':</strong><br>' + String(address).replace(/\n/g, '<br>') + '</p>';
                    if (hours) html += '<p><strong>' + labelHours + ':</strong><br>' + String(hours).replace(/\n/g, '<br>') + '</p>';
                    if (phone) html += '<p><strong>' + labelPhone + ':</strong> ' + phone + '</p>';
                    if (email) html += '<p><strong>' + labelEmail + ':</strong> ' + email + '</p>';
            
                    $('#wcps-store-details').html(html).stop(true, true).slideDown(200);
                }
            
                function moveSelectorToCorrectPosition() {
                    if (selectorMoved) return;
                    var $wrapper = $('#wcps-store-wrapper');
                    var $wdTableWrapper = $('.wd-table-wrapper');
                    var $payment = $('#payment');
            
                    if ($wdTableWrapper.length) {
                        $wrapper.insertAfter($wdTableWrapper);
                        selectorMoved = true;
                    } else if ($payment.length) {
                        $wrapper.insertBefore($payment);
                        selectorMoved = true;
                    }
                }
            
                function toggleStoreSelector() {
                    var selectedMethod = $('input[name^="shipping_method"]:checked').val();
                    var isPickup = selectedMethod && selectedMethod.indexOf('pickup_store') !== -1;
            
                    if (isPickup) {
                        $('#wcps-store-wrapper').stop(true, true).slideDown(200);
                        $('#pickup_store_id').prop('required', true);
                        moveSelectorToCorrectPosition();
                        displayStoreDetails();
                    } else {
                        $('#wcps-store-wrapper').stop(true, true).slideUp(200);
                        $('#pickup_store_id').prop('required', false);
                        $('#wcps-store-details').hide().html('');
                    }
                }
            
                $(document.body).on('change', 'input[name^="shipping_method"]', function() {
                    setTimeout(toggleStoreSelector, 50);
                });
            
                $(document.body).on('change', '#pickup_store_id', function() {
                    displayStoreDetails();
                });
            
                $(document.body).on('updated_checkout', function() {
                    setTimeout(function() {
                        moveSelectorToCorrectPosition();
                        toggleStoreSelector();
                    }, 200);
                });
            
                $(document).ready(function() {
                    setTimeout(function() {
                        moveSelectorToCorrectPosition();
                        toggleStoreSelector();
                    }, 300);
                });
            })(jQuery);
        </script>
        <?php
    }
    
    public function validate_store_selection() {
        $chosen_methods = WC()->session->get('chosen_shipping_methods');
        $chosen_method = is_array($chosen_methods) ? $chosen_methods[0] : $chosen_methods;
        
        if (strpos($chosen_method, 'pickup_store') !== false) {
            if (!isset($_POST['pickup_store_id']) || empty($_POST['pickup_store_id'])) {
                wc_add_notice(__('Please select a store for order pickup.', 'wc-pickup-store'), 'error');
            }
        }
    }
    
    public function save_selected_store($order, $data) {
        $chosen_methods = WC()->session->get('chosen_shipping_methods');
        $chosen_method = is_array($chosen_methods) ? $chosen_methods[0] : $chosen_methods;
        
        if (strpos($chosen_method, 'pickup_store') !== false && isset($_POST['pickup_store_id']) && !empty($_POST['pickup_store_id'])) {
            $store_id = intval($_POST['pickup_store_id']);
            $stores = WC_Store_CPT::get_stores();
            
            foreach ($stores as $store) {
                if ($store['id'] == $store_id) {
                    $order->update_meta_data('_pickup_store_id', $store_id);
                    $order->update_meta_data('_pickup_store_name', $store['name']);
                    $order->update_meta_data('_pickup_store_address', $store['address']);
                    $order->update_meta_data('_pickup_store_hours', $store['hours']);
                    $order->update_meta_data('_pickup_store_phone', $store['phone']);
                    $order->update_meta_data('_pickup_store_email', $store['email']);
                    break;
                }
            }
        }
    }
    
    public function update_session_store($post_data) {
        parse_str($post_data, $form_data);
        if (isset($form_data['pickup_store_id']) && !empty($form_data['pickup_store_id'])) {
            WC()->session->set('chosen_pickup_store', intval($form_data['pickup_store_id']));
        }
    }
    
/**
 * Afișează informațiile complete în email
 */
public function display_pickup_info_email($order, $sent_to_admin, $plain_text, $email) {
    $store_name = $order->get_meta('_pickup_store_name');
    
    if (!$store_name || $sent_to_admin) {
        return;
    }
    
    $store_address = $order->get_meta('_pickup_store_address');
    $store_hours = $order->get_meta('_pickup_store_hours');
    $store_phone = $order->get_meta('_pickup_store_phone');
    $store_email = $order->get_meta('_pickup_store_email');
    $notice = get_option('wcps_pickup_notice', '');
    $thankyou_title = get_option('wcps_thankyou_title', 'Pickup Order Information');
    
    // Get customizable labels
    $label_store = get_option('wcps_label_store', '🏪 Store');
    $label_address = get_option('wcps_label_address', '📍 Address');
    $label_hours = get_option('wcps_label_hours', '🕒 Business Hours');
    $label_phone = get_option('wcps_label_phone', '📞 Phone');
    $label_email = get_option('wcps_label_email', '📧 Email');
    
    if ($plain_text) {
        echo "\n========================================\n";
        echo esc_html($thankyou_title) . "\n";
        echo esc_html($label_store) . ': ' . $store_name . "\n";
        if ($store_address) echo esc_html($label_address) . ': ' . strip_tags($store_address) . "\n";
        if ($store_hours) echo esc_html($label_hours) . ': ' . strip_tags($store_hours) . "\n";
        if ($store_phone) echo esc_html($label_phone) . ': ' . $store_phone . "\n";
        if ($store_email) echo esc_html($label_email) . ': ' . $store_email . "\n";
        if ($notice) echo strip_tags($notice) . "\n";
        echo "========================================\n";
    } else {
        echo '<div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-left: 4px solid #7ad03a;">';
        echo '<h3>' . esc_html($thankyou_title) . '</h3>';
        echo '<p><strong>' . esc_html($label_store) . ':</strong><br>' . esc_html($store_name) . '</p>';
        if ($store_address) echo '<p><strong>' . esc_html($label_address) . ':</strong><br>' . nl2br(esc_html($store_address)) . '</p>';
        if ($store_hours) echo '<p><strong>' . esc_html($label_hours) . ':</strong><br>' . nl2br(esc_html($store_hours)) . '</p>';
        if ($store_phone) echo '<p><strong>' . esc_html($label_phone) . ':</strong><br>' . esc_html($store_phone) . '</p>';
        if ($store_email) echo '<p><strong>' . esc_html($label_email) . ':</strong><br>' . esc_html($store_email) . '</p>';
        if ($notice) echo '<p><strong>' . __('Note:', 'wc-pickup-store') . '</strong><br>' . wp_kses_post($notice) . '</p>';
        echo '</div>';
    }
}

    public function enqueue_scripts() {
        if (is_checkout()) {
            $css_file = WCPS_PLUGIN_DIR . 'assets/css/pickup-style.css';
            
            if (file_exists($css_file)) {
                wp_enqueue_style(
                    'wcps-style',
                    WCPS_PLUGIN_URL . 'assets/css/pickup-style.css',
                    array(),
                    filemtime($css_file)
                );
            }
        }
    }
    
    /**
     * Admin display - doar numele magazinului (simplu)
     */
    public function display_pickup_admin_simple($order) {
        $store_name = $order->get_meta('_pickup_store_name');
        $label_store = get_option('wcps_label_store', '🏪 Store');
        
        if (!$store_name) {
            return;
        }
        ?>
        
        <div class="wcps-admin-pickup-simple">
            <p><strong><?php _e(esc_html($label_store), 'wc-pickup-store'); ?></strong>:  <?php echo esc_html($store_name); ?></p>
        </div>
        
        <style>
            .wcps-admin-pickup-simple {
                margin-top: 10px;
                padding: 8px 12px;
                background: #e8f5e9;
                border-left: 3px solid #7ad03a;
                border-radius: 4px;
            }
            .wcps-admin-pickup-simple p {
                margin: 0;
            }
        </style>
        <?php
    }
    
    /**
     * Adaugă coloană în lista de comenzi
     */
    public function add_pickup_store_column($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'order_total') {
                $new_columns['pickup_store'] = __('Pickup Store', 'wc-pickup-store');
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Afișează conținutul coloanei
     */
    public function display_pickup_store_column($column, $post_id) {
        if ($column !== 'pickup_store') {
            return;
        }
        
        $order = wc_get_order($post_id);
        $store_name = $order->get_meta('_pickup_store_name');
        
        if ($store_name) {
            echo '<span class="wcps-order-store">🏪 ' . esc_html($store_name) . '</span>';
        } else {
            echo '—';
        }
    }
    
/**
 * Afișează informațiile complete pe pagina de mulțumire
 */
public function display_pickup_info($order_id) {
    $order = wc_get_order($order_id);
    $store_name = $order->get_meta('_pickup_store_name');
    
    if (!$store_name) {
        return;
    }
    
    $store_address = $order->get_meta('_pickup_store_address');
    $store_hours = $order->get_meta('_pickup_store_hours');
    $store_phone = $order->get_meta('_pickup_store_phone');
    $store_email = $order->get_meta('_pickup_store_email');
    $notice = get_option('wcps_pickup_notice', '');
    $thankyou_title = get_option('wcps_thankyou_title', 'Pickup Order Information');
    
    // Get customizable labels
    $label_store = get_option('wcps_label_store', '🏪 Store');
    $label_address = get_option('wcps_label_address', '📍 Address');
    $label_hours = get_option('wcps_label_hours', '🕒 Business Hours');
    $label_phone = get_option('wcps_label_phone', '📞 Phone');
    $label_email = get_option('wcps_label_email', '📧 Email');
    ?>
    
    <div class="wcps-pickup-info">
        <h3>📦 <?php echo esc_html($thankyou_title); ?></h3>
        <p><strong><?php echo esc_html($label_store); ?>:</strong><br><?php echo esc_html($store_name); ?></p>
        
        <?php if ($store_address): ?>
            <p><strong><?php echo esc_html($label_address); ?>:</strong><br><?php echo nl2br(esc_html($store_address)); ?></p>
        <?php endif; ?>
        
        <?php if ($store_hours): ?>
            <p><strong><?php echo esc_html($label_hours); ?>:</strong><br><?php echo nl2br(esc_html($store_hours)); ?></p>
        <?php endif; ?>
        
        <?php if ($store_phone): ?>
            <p><strong><?php echo esc_html($label_phone); ?>:</strong><br><?php echo esc_html($store_phone); ?></p>
        <?php endif; ?>
        
        <?php if ($store_email): ?>
            <p><strong><?php echo esc_html($label_email); ?>:</strong><br><?php echo esc_html($store_email); ?></p>
        <?php endif; ?>
        
        <?php if ($notice): ?>
            <p><strong>📋 <?php _e('Note:', 'wc-pickup-store'); ?></strong><br><?php echo wp_kses_post($notice); ?></p>
        <?php endif; ?>
    </div>
    
    <style>
        .wcps-pickup-info {
            margin-top: 20px;
            padding: 20px;
            background: #f7fbf3;
            border: 1px solid #d9edc7;
            border-left: 4px solid #7ad03a;
            border-radius: 12px;
        }
        .wcps-pickup-info h3 {
            margin: 0 0 12px;
            font-size: 18px;
        }
        .wcps-pickup-info p {
            margin: 8px 0;
        }
    </style>
    <?php
}
}
