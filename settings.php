<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$AR_ONE_TIME_COUPON_ENABLED = 'ar_custom_coupon_gen_enabled';
$AR_ONE_TIME_COUPON_PREFIX = 'ar_custom_coupon_gen_prefix';

if ( ! class_exists( 'WooCommerce_Coupon_Generator_Settings' ) ) {

	class WooCommerce_Coupon_Generator_Settings {

		public function __construct() {
            add_action('save_post', array($this, 'save_postdata'));
            add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
            add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
            add_action('admin_notices', [$this, 'maybe_show_admin_notice']);
        }

        public function add_meta_boxes() {
            add_meta_box('ar_meta_coupon_box', 'One time coupons', array($this, 'ar_add_meta_coupon_box'), 'shop_coupon', 'side', 'high');
        }

        public function enqueue_scripts($hook) {
            global $AR_ONE_TIME_COUPON_ENABLED, $AR_ONE_TIME_COUPON_PREFIX;

            global $post;

            if (($hook !== 'post.php' && $hook !== 'post-new.php') || !$post || $post->post_type !== 'shop_coupon') {
                return;
            }

            wp_enqueue_script(
                'woo-coupon-gen-admin',
                plugins_url('/assets/js/admin_scripts.js', __FILE__),
                ['jquery'],
                '0.1.0',
                true
            );

            wp_localize_script('woo-coupon-gen-admin', 'woo_copoun_generator', [
                'coupon_enabled_id' => $AR_ONE_TIME_COUPON_ENABLED,
                'coupon_prefix_id' => $AR_ONE_TIME_COUPON_PREFIX,
                'mailpoet_shortcode' => ar_mailpoet_coupon_gen_shortcode(),
                'fluentcrm_smartcode' => ar_fluentcrm_coupon_gen_smartcode(),
            ]);
        }

        public function ar_add_meta_coupon_box()
        {
            global $post, $AR_ONE_TIME_COUPON_ENABLED, $AR_ONE_TIME_COUPON_PREFIX;

            $one_time_coupon_enabled = $this->load_checkbox_value($post->ID, $AR_ONE_TIME_COUPON_ENABLED);
            $one_time_coupon_prefix = get_post_meta($post->ID, ar_key($AR_ONE_TIME_COUPON_PREFIX), true);
            if (empty($one_time_coupon_prefix))
                $one_time_coupon_prefix = "coupon_";

            $post_title = get_the_title($post->ID);

            include(dirname(__FILE__) . '/meta_box_coupon.php');
        }

        public function load_checkbox_value($post_id, $id)
        {
            $value = get_post_meta($post_id, ar_key($id), true);
            if ($value == 'true') {
                $value = 'checked="checked"';
            } else {
                $value = '';
            }

            return $value;
        }

        public function save_postdata($post_id)
        {
            global $AR_ONE_TIME_COUPON_ENABLED, $AR_ONE_TIME_COUPON_PREFIX;

            // Security check
            if (!isset($_POST['one_time_coupon_nonce'])) {
                return $post_id;
            }
            if (!wp_verify_nonce($_POST['one_time_coupon_nonce'], 'one_time_coupon')) {
                return $post_id;
            }
            
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }

            $this->save_checkbox_value($post_id, $AR_ONE_TIME_COUPON_ENABLED);
            $this->save_input_value($post_id, $AR_ONE_TIME_COUPON_PREFIX);

            if (isset($_POST['ar_delete_child_coupons']) && $_POST['ar_delete_child_coupons'] === '1') {
                $deleted_count = $this->delete_child_coupons($post_id);
                $this->set_admin_notice($deleted_count);
            }

            return $post_id;
        }

        public function save_checkbox_value($post_id, $id)
        {
            if (isset($_POST[$id]) && $_POST[$id] == 'true') {
                update_post_meta($post_id, ar_key($id), 'true');
            } else {
                delete_post_meta($post_id, ar_key($id));
            }
        }

        public function save_input_value($post_id, $id)
        {
            if (isset($_POST[$id])) {
                update_post_meta($post_id, ar_key($id), sanitize_text_field($_POST[$id]));
            } else {
                delete_post_meta($post_id, ar_key($id));
            }
        }

        private function delete_child_coupons($base_coupon_id)
        {
            global $wpdb, $AR_ONE_TIME_COUPON_PREFIX;

            $base_coupon = get_post($base_coupon_id);
            if (!$base_coupon || $base_coupon->post_type !== 'shop_coupon') {
                return 0;
            }

            $prefix = get_post_meta($base_coupon_id, ar_key($AR_ONE_TIME_COUPON_PREFIX), true);
            if (empty($prefix)) {
                return 0;
            }

            $base_title = $base_coupon->post_title;
            $content_prefix = 'Generated from ' . $base_title;

            $coupon_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT ID
                     FROM $wpdb->posts
                     WHERE post_type = 'shop_coupon'
                     AND post_status <> 'trash'
                     AND ID <> %d
                     AND post_title LIKE %s
                     AND post_content LIKE %s",
                    $base_coupon_id,
                    $wpdb->esc_like($prefix) . '%',
                    $wpdb->esc_like($content_prefix) . '%'
                )
            );

            if (empty($coupon_ids)) {
                return 0;
            }

            $deleted = 0;
            foreach ($coupon_ids as $coupon_id) {
                $result = wp_delete_post((int) $coupon_id, true);
                if ($result) {
                    $deleted++;
                }
            }

            return $deleted;
        }

        private function set_admin_notice($deleted_count)
        {
            set_transient(
                'ar_coupon_delete_notice_' . get_current_user_id(),
                (int) $deleted_count,
                120
            );
        }

        public function maybe_show_admin_notice()
        {
            if (!is_admin()) {
                return;
            }

            if (!function_exists('get_current_screen')) {
                return;
            }

            $screen = get_current_screen();
            if (!$screen || $screen->base !== 'post' || $screen->post_type !== 'shop_coupon') {
                return;
            }

            $key = 'ar_coupon_delete_notice_' . get_current_user_id();
            $deleted_count = get_transient($key);
            if ($deleted_count === false) {
                return;
            }

            delete_transient($key);
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo esc_html(sprintf('Deleted %d generated child coupon(s).', (int) $deleted_count));
            echo '</p></div>';
        }
    }
}

function ar_key($id){
    return '_ar_' . $id;
}

new WooCommerce_Coupon_Generator_Settings();
?>
