<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Store_CPT {

    const POST_TYPE = 'wc_pickup_store';

    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_store_metaboxes'));
        add_action('save_post_' . self::POST_TYPE, array($this, 'save_store_metaboxes'));
        
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', array($this, 'add_store_columns'));
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array($this, 'display_store_columns'), 10, 2);
    }

    public function register_post_type() {
        $labels = array(
            'name'               => __('Pickup Stores', 'wc-pickup-store-main'),
            'singular_name'      => __('Store', 'wc-pickup-store-main'),
            'menu_name'          => __('Pickup Stores', 'wc-pickup-store-main'),
            'add_new'            => __('Add Store', 'wc-pickup-store-main'),
            'add_new_item'       => __('Add New Store', 'wc-pickup-store-main'),
            'edit_item'          => __('Edit Store', 'wc-pickup-store-main'),
            'new_item'           => __('New Store', 'wc-pickup-store-main'),
            'view_item'          => __('View Store', 'wc-pickup-store-main'),
            'search_items'       => __('Search Stores', 'wc-pickup-store-main'),
            'not_found'          => __('No stores found', 'wc-pickup-store-main'),
            'not_found_in_trash' => __('No stores found in trash', 'wc-pickup-store-main'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => 'woocommerce',
            'show_in_nav_menus'   => false,
            'supports'            => array('title'),
            'capability_type'     => 'post',
            'has_archive'         => false,
            'menu_icon'           => 'dashicons-location-alt',
        );

        register_post_type(self::POST_TYPE, $args);
    }

    public function add_store_metaboxes() {
        add_meta_box(
            'wcps_store_details',
            __('Store Details', 'wc-pickup-store-main'),
            array($this, 'render_store_metabox'),
            self::POST_TYPE,
            'normal',
            'high'
        );
    }

    public function render_store_metabox($post) {
        wp_nonce_field('wcps_store_metabox', 'wcps_store_nonce');
        
        $address = get_post_meta($post->ID, '_store_address', true);
        $hours = get_post_meta($post->ID, '_store_hours', true);
        $phone = get_post_meta($post->ID, '_store_phone', true);
        $email = get_post_meta($post->ID, '_store_email', true);
        ?>
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px;"><?php esc_html_e('Store Address', 'wc-pickup-store-main'); ?> *</label>
            <textarea name="store_address" rows="3" style="width: 100%; max-width: 500px;" placeholder="<?php esc_attr_e('Main Street, No. 123, City', 'wc-pickup-store-main'); ?>"><?php echo esc_textarea($address); ?></textarea>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px;"><?php esc_html_e('Business Hours', 'wc-pickup-store-main'); ?> *</label>
            <textarea name="store_hours" rows="4" style="width: 100%; max-width: 500px;" placeholder="<?php esc_attr_e('Monday - Friday: 09:00 - 18:00&#10;Saturday: 10:00 - 14:00&#10;Sunday: Closed', 'wc-pickup-store-main'); ?>"><?php echo esc_textarea($hours); ?></textarea>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px;"><?php esc_html_e('Phone Number', 'wc-pickup-store-main'); ?></label>
            <input type="text" name="store_phone" value="<?php echo esc_attr($phone); ?>" style="width: 100%; max-width: 500px;" placeholder="<?php esc_attr_e('+1 234 567 890', 'wc-pickup-store-main'); ?>" />
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px;"><?php esc_html_e('Email Address', 'wc-pickup-store-main'); ?></label>
            <input type="email" name="store_email" value="<?php echo esc_attr($email); ?>" style="width: 100%; max-width: 500px;" placeholder="<?php esc_attr_e('store@example.com', 'wc-pickup-store-main'); ?>" />
        </div>
        <?php
    }

    public function save_store_metaboxes($post_id) {
        if (!isset($_POST['wcps_store_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wcps_store_nonce'])), 'wcps_store_metabox')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['store_address'])) {
            update_post_meta($post_id, '_store_address', sanitize_textarea_field(wp_unslash($_POST['store_address'])));
        }
        if (isset($_POST['store_hours'])) {
            update_post_meta($post_id, '_store_hours', sanitize_textarea_field(wp_unslash($_POST['store_hours'])));
        }
        if (isset($_POST['store_phone'])) {
            update_post_meta($post_id, '_store_phone', sanitize_text_field(wp_unslash($_POST['store_phone'])));
        }
        if (isset($_POST['store_email'])) {
            update_post_meta($post_id, '_store_email', sanitize_email(wp_unslash($_POST['store_email'])));
        }
    }

    public function add_store_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['address'] = __('Address', 'wc-pickup-store-main');
                $new_columns['hours'] = __('Hours', 'wc-pickup-store-main');
                $new_columns['phone'] = __('Phone', 'wc-pickup-store-main');
                $new_columns['email'] = __('Email', 'wc-pickup-store-main');
            }
        }
        return $new_columns;
    }

    public function display_store_columns($column, $post_id) {
        if ($column === 'address') {
            echo esc_html(get_post_meta($post_id, '_store_address', true));
        }
        if ($column === 'hours') {
            echo nl2br(esc_html(substr(get_post_meta($post_id, '_store_hours', true), 0, 60)));
        }
        if ($column === 'phone') {
            echo esc_html(get_post_meta($post_id, '_store_phone', true));
        }
        if ($column === 'email') {
            echo esc_html(get_post_meta($post_id, '_store_email', true));
        }
    }

    public static function get_stores() {
        $stores = get_posts(array(
            'post_type' => self::POST_TYPE,
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));
        
        $stores_data = array();
        foreach ($stores as $store) {
            $stores_data[] = array(
                'id' => $store->ID,
                'name' => $store->post_title,
                'address' => get_post_meta($store->ID, '_store_address', true),
                'hours' => get_post_meta($store->ID, '_store_hours', true),
                'phone' => get_post_meta($store->ID, '_store_phone', true),
                'email' => get_post_meta($store->ID, '_store_email', true),
            );
        }
        return $stores_data;
    }
}