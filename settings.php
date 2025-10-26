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
        }

        public function add_meta_boxes() {
            add_meta_box('ar_meta_coupon_box', 'One time coupons', array($this, 'ar_add_meta_coupon_box'), 'shop_coupon', 'side', 'high');
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
            $this->save_checkbox_value($post_id, $AR_ONE_TIME_COUPON_ENABLED);
            $this->save_input_value($post_id, $AR_ONE_TIME_COUPON_PREFIX);

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
                update_post_meta($post_id, ar_key($id), $_POST[$id]);
            } else {
                delete_post_meta($post_id, ar_key($id));
            }
        }
    }
}

function ar_key($id){
    return '_ar_' . $id;
}

new WooCommerce_Coupon_Generator_Settings();
?>
