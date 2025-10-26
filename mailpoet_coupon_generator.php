<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Mailpoet_Coupon_Generator' ) ) {

    $AR_MAILPOET_COUPON_CUSTOM_SHORTCODE_PREFIX = 'custom:coupon_';

	class Mailpoet_Coupon_Generator {

        private $SETTINGS_PAGE = 'custom_coupon_generator_settings';

		public function __construct() {
            // https://kb.mailpoet.com/article/160-create-a-custom-shortcode
            add_filter('mailpoet_newsletter_shortcode', array($this, 'ww_mailpoet_custom_coupon_shortcode'), 10, 6);
        }

        public function ww_mailpoet_custom_coupon_shortcode($shortcode, $newsletter, $subscriber, $queue, $newsletter_body, $arguments) {
            global $AR_COUPON_CUSTOM_SHORTCODE_PREFIX, $AR_COUPON_GEN_PREFIX_OPTION, $AR_ONE_TIME_COUPON_PREFIX;

            $pattern = '/\['.$AR_COUPON_CUSTOM_SHORTCODE_PREFIX.'([^\]]+)\]/';

            if (!preg_match($pattern, $shortcode, $matches)) {
                return $shortcode;
            }
        
            $orig_coupon = $matches[1]; 
            $orig_coupon_id = $this->ww_get_coupon($orig_coupon);

            if(!$orig_coupon_id) {
                error_log('Coupon could not be generated. Original coupon '.$orig_coupon.' not found');
                return '';     
            }
        
            $prefix = get_post_meta($orig_coupon_id, ar_key($AR_ONE_TIME_COUPON_PREFIX), true );
            if (empty($prefix)) {
                error_log('Prefix for coupon ' . $orig_coupon . ' not found! Using default coupon_ prefix.');
                $prefix = 'coupon_';
            }

            if (is_null($queue)) {
                return $prefix . 'preview-only';
            }

            $coupon_name = $this->ww_create_coupon_name($prefix);
            $create_coupon_name_count = 0;
        
            while($this->ww_get_coupon($coupon_name) && $create_coupon_name_count < 30){
                $coupon_name = $this->ww_create_coupon_name($prefix);
                $create_coupon_name_count++;
            }
        
            if($create_coupon_name_count >= 10) {
                error_log('Coupon could not be generated! Failed to find a free name for the new coupon.'); 
                return '';
            }
        
            $post = array(
                'post_content'   => "Generated from ".$orig_coupon,
                'post_name'      => "Name",
                'post_title'     => $coupon_name,
                'post_status'    => 'publish',
                'post_type'      => 'shop_coupon'
            );
            $coupon_id = wp_insert_post( $post );
            if ($coupon_id == 0) {
                error_log('Coupon could not be generated! Could not create new coupon '.$coupon_name.' from coupon '.$orig_coupon); 
                return '';
            }
        
            $this->ww_copy_coupon_meta($orig_coupon_id, $coupon_id);
        
            return $coupon_name;
        }

        private function ww_get_coupon($coupon_name) {
            global $wpdb;
            return $wpdb->get_var( $wpdb->prepare( "
                    SELECT $wpdb->posts.ID
                    FROM $wpdb->posts
                    WHERE $wpdb->posts.post_type = 'shop_coupon'
                    AND $wpdb->posts.post_status = 'publish'
                    AND $wpdb->posts.post_title = '%s'
                 ", $coupon_name) );
        }

        private function ww_create_coupon_name($coupon_prefix) {
            return $coupon_prefix . wp_rand(10, 100000);
        }

        private function ww_copy_coupon_meta($orig_id, $id) {

            update_post_meta( $id, 'discount_type',         get_post_meta( $orig_id, 'discount_type', true) );
            update_post_meta( $id, 'coupon_amount',         get_post_meta( $orig_id, 'coupon_amount', true) );
            update_post_meta( $id, 'individual_use',        get_post_meta( $orig_id, 'individual_use', true) );
            update_post_meta( $id, 'product_ids',           get_post_meta( $orig_id, 'product_ids', true) );
            update_post_meta( $id, 'exclude_product_ids',   get_post_meta( $orig_id, 'exclude_product_ids', true) );
            update_post_meta( $id, 'usage_limit',           get_post_meta( $orig_id, 'usage_limit', true) );
            update_post_meta( $id, 'usage_limit_per_user',  get_post_meta( $orig_id, 'usage_limit_per_user', true) );
            update_post_meta( $id, 'limit_usage_to_x_items',get_post_meta( $orig_id, 'limit_usage_to_x_items', true) );
            update_post_meta( $id, 'usage_count',           get_post_meta( $orig_id, 'usage_count', true) );
            update_post_meta( $id, 'expiry_date',           get_post_meta( $orig_id, 'expiry_date', true) );
            update_post_meta( $id, 'free_shipping',         get_post_meta( $orig_id, 'free_shipping', true) );
            update_post_meta( $id, 'product_categories',    get_post_meta( $orig_id, 'product_categories', true) );
            update_post_meta( $id, 'exclude_product_categories', get_post_meta( $orig_id, 'exclude_product_categories', true) );
            update_post_meta( $id, 'exclude_sale_items',    get_post_meta( $orig_id, 'exclude_sale_items', true) );
            update_post_meta( $id, 'minimum_amount',        get_post_meta( $orig_id, 'minimum_amount', true) );
            update_post_meta( $id, 'maximum_amount',        get_post_meta( $orig_id, 'maximum_amount', true) );
            update_post_meta( $id, 'customer_email',        get_post_meta( $orig_id, 'customer_email', true) );
        }
    }

    function ar_mailpoet_coupon_gen_smart_code() {
        global $AR_MAILPOET_COUPON_CUSTOM_SHORTCODE_PREFIX;
        return $AR_MAILPOET_COUPON_CUSTOM_SHORTCODE_PREFIX;
    }

    new Mailpoet_Coupon_Generator();
}
?>