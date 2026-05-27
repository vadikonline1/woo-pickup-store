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
            'name'               => __('Pickup Stores', 'wc-pickup-store'),
            'singular_name'      => __('Store', 'wc-pickup-store'),
            'menu_name'          => __('Pickup Stores', 'wc-pickup-store'),
            'add_new'            => __('Add Store', 'wc-pickup-store'),
            'add_new_item'       => __('Add New Store', 'wc-pickup-store'),
            'edit_item'          => __('Edit Store', 'wc-pickup-store'),
            'new_item'           => __('New Store', 'wc-pickup-store'),
            'view_item'          => __('View Store', 'wc-pickup-store'),
            'search_items'       => __('Search Stores', 'wc-pickup-store'),
            'not_found'          => __('No stores found', 'wc-pickup-store'),
            'not_found_in_trash' => __('No stores found in trash', 'wc-pickup-store'),
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
            __('Store Details', 'wc-pickup-store'),
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
            <label style="display: block; font-weight: 600; margin-bottom: 5px;"><?php _e('Store Address', 'wc-pickup-store'); ?> *</label>
            <textarea name="store_address" rows="3" style="width: 100%; max-width: 500px;" placeholder="Main Street, No. 123, City"><?php echo esc_textarea($address); ?></textarea>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px;"><?php _e('Business Hours', 'wc-pickup-store'); ?> *</label>
            <textarea name="store_hours" rows="4" style="width: 100%; max-width: 500px;" placeholder="Monday - Friday: 09:00 - 18:00&#10;Saturday: 10:00 - 14:00&#10;Sunday: Closed"><?php echo esc_textarea($hours); ?></textarea>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px;"><?php _e('Phone Number', 'wc-pickup-store'); ?></label>
            <input type="text" name="store_phone" value="<?php echo esc_attr($phone); ?>" style="width: 100%; max-width: 500px;" placeholder="+1 234 567 890" />
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px;"><?php _e('Email Address', 'wc-pickup-store'); ?></label>
            <input type="email" name="store_email" value="<?php echo esc_attr($email); ?>" style="width: 100%; max-width: 500px;" placeholder="store@example.com" />
        </div>
        <?php
    }

    public function save_store_metaboxes($post_id) {
        if (!isset($_POST['wcps_store_nonce']) || !wp_verify_nonce($_POST['wcps_store_nonce'], 'wcps_store_metabox')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        if (isset($_POST['store_address'])) update_post_meta($post_id, '_store_address', sanitize_textarea_field($_POST['store_address']));
        if (isset($_POST['store_hours'])) update_post_meta($post_id, '_store_hours', sanitize_textarea_field($_POST['store_hours']));
        if (isset($_POST['store_phone'])) update_post_meta($post_id, '_store_phone', sanitize_text_field($_POST['store_phone']));
        if (isset($_POST['store_email'])) update_post_meta($post_id, '_store_email', sanitize_email($_POST['store_email']));
    }

    public function add_store_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['address'] = __('Address', 'wc-pickup-store');
                $new_columns['hours'] = __('Hours', 'wc-pickup-store');
                $new_columns['phone'] = __('Phone', 'wc-pickup-store');
                $new_columns['email'] = __('Email', 'wc-pickup-store');
            }
        }
        return $new_columns;
    }

    public function display_store_columns($column, $post_id) {
        if ($column === 'address') echo esc_html(get_post_meta($post_id, '_store_address', true));
        if ($column === 'hours') echo nl2br(esc_html(substr(get_post_meta($post_id, '_store_hours', true), 0, 60)));
        if ($column === 'phone') echo esc_html(get_post_meta($post_id, '_store_phone', true));
        if ($column === 'email') echo esc_html(get_post_meta($post_id, '_store_email', true));
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
