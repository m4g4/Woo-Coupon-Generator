<?php

use FluentCrm\App\Api\Classes\Extender;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Smartcode: {{generate_coupon:BASECOUPON}}
 * Generates a WooCommerce coupon for each FluentCRM contact.
 */

if (!class_exists('FluentCRM_Coupon_Generator')) {

    $AR_FLUENTCRM_COUPON_CUSTOM_SHORTCODE_KEY = 'ar';
    $AR_FLUENTCRM_COUPON_CUSTOM_SHORTCODE_PREFIX = 'generate_coupon';

    class FluentCRM_Coupon_Generator
    {
        private $shortcode_key;
        private $shortcode_prefix;

        private $coupon_code_placeholder = 'COUPON_CODE_HERE';
        private $extender;

        public function __construct($shortcode_key, $shortcode_prefix)
        {
            $this->shortcode_key = $shortcode_key;
            $this->shortcode_prefix = $shortcode_prefix;
            add_action('init', [$this, 'maybe_register_smartcode'], 100);

            $this->extender = new Extender();
        }

        public function maybe_register_smartcode()
        {
            try {
                $this->extender->addSmartCode(
                    $this->shortcode_key,
                    'Woo Coupon',
                    [$this->shortcode_prefix .':'.$this->coupon_code_placeholder => 'Generate unique coupon'],
                    [$this, 'handle_coupon_smartcode']
                );
            } catch (Exception $e) {
                error_log('FluentCRM_Coupon_Generator: Error registering smartcode: ' . $e->getMessage());
            }
        }

        public function handle_coupon_smartcode($code, $subscriber)
        {
            // Clean the code by removing {{ and }} delimiters
            $clean_code = trim($code, '{}');
            $parts = explode(':', $clean_code);

            $is_preview = $this->is_email_preview($subscriber);

            if (count($parts) < 2) {
                if ($is_preview) {
                    return 'INVALID COUPON SMART CODE. MISSING COUPON CODE!';
                }
                error_log('FluentCRM_Coupon_Generator: Invalid smartcode format.');
                return '';
            }

            $base_coupon = sanitize_text_field($parts[1]);

            if ($base_coupon === $this->coupon_code_placeholder) {
                if ($is_preview) {
                    return 'INVALID COUPON SMART CODE. YOU DID NOT MODIFY THE COUPON CODE!';
                }

                error_log('FluentCRM_Coupon_Generator: Coupon code not modified.');
                return '';
            }

            return $this->get_or_create_coupon($base_coupon, $subscriber, $is_preview);
        }

        private function get_or_create_coupon($base_coupon, $subscriber, $is_preview)
        {
            if (!$is_preview) {
                $existing = fluentcrm_get_subscriber_meta($subscriber->id, 'coupon_' . $base_coupon);
                if ($existing) {
                    return $existing;
                }
            }

            $orig_id = $this->get_coupon_id_by_title($base_coupon);
            if (!$orig_id) {
                error_log("FluentCRM_Coupon_Generator: Base coupon '{$base_coupon}' not found.");
                if ($is_preview) {
                    return 'INVALID COUPON SMART CODE. COUPON CODE DOES NOT EXIST!';
                }
                return '';
            }

            $prefix = get_post_meta($orig_id, '_coupon_prefix', true) ?: 'coupon_';
            $new_code = $this->create_coupon_name($prefix);

            $post = [
                'post_title'   => $new_code,
                'post_content' => 'Generated from ' . $base_coupon . ' for ' . $subscriber->email,
                'post_status'  => 'publish',
                'post_type'    => 'shop_coupon'
            ];

            $new_id = wp_insert_post($post);
            if (!$new_id) {
                error_log("FluentCRM_Coupon_Generator: Failed to create coupon '{$new_code}' from base '{$base_coupon}'.");
                return '';
            }

            $this->copy_coupon_meta($orig_id, $new_id);

            fluentcrm_update_subscriber_meta($subscriber->id, 'coupon_' . $base_coupon, $new_code);

            return $new_code;
        }

        private function get_coupon_id_by_title($title)
        {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare("
                SELECT ID FROM $wpdb->posts
                WHERE post_type = 'shop_coupon'
                AND post_status = 'publish'
                AND post_title = %s
            ", $title));
        }

        private function create_coupon_name($prefix)
        {
            return $prefix . wp_rand(10000, 999999);
        }

        private function copy_coupon_meta($orig_id, $new_id)
        {
            $meta_keys = [
                'discount_type', 'coupon_amount', 'individual_use', 'product_ids',
                'exclude_product_ids', 'usage_limit', 'usage_limit_per_user',
                'limit_usage_to_x_items', 'expiry_date', 'free_shipping',
                'product_categories', 'exclude_product_categories',
                'exclude_sale_items', 'minimum_amount', 'maximum_amount', 'customer_email'
            ];

            foreach ($meta_keys as $key) {
                $value = get_post_meta($orig_id, $key, true);
                update_post_meta($new_id, $key, $value);
                error_log('FluentCRM_Coupon_Generator: Copied meta ' . $key . ' = ' . $value);
            }
        }

        private function is_email_preview($subscriber)
        {
            // Check if subscriber is invalid or a test subscriber
            if (!$subscriber || !isset($subscriber->id) || !isset($subscriber->email) || empty($subscriber->email)) {
                error_log('FluentCRM_Coupon_Generator: Invalid or missing subscriber data, assuming preview.');
                return true;
            }

            // Check if this is an AJAX request (common for previews)
            if (defined('DOING_AJAX') && DOING_AJAX) {
                error_log('FluentCRM_Coupon_Generator: AJAX request detected, assuming preview.');
                return true;
            }

            // Optional: Check query vars or context (customize based on FluentCRM behavior)
            if (isset($_GET['fluentcrm_preview']) || isset($_GET['preview'])) {
                error_log('FluentCRM_Coupon_Generator: Preview query var detected.');
                return true;
            }

            return false;
        }
    }

    function ar_fluentcrm_coupon_gen_smart_code() {
        global $AR_FLUENTCRM_COUPON_CUSTOM_SHORTCODE_KEY, $AR_FLUENTCRM_COUPON_CUSTOM_SHORTCODE_PREFIX;
        return $AR_FLUENTCRM_COUPON_CUSTOM_SHORTCODE_KEY.'.'.$AR_FLUENTCRM_COUPON_CUSTOM_SHORTCODE_PREFIX;
    }

    function ar_is_fluentcrm_active() {
        return is_plugin_active('fluent-crm/fluent-crm.php');
    }

    new FluentCRM_Coupon_Generator($AR_FLUENTCRM_COUPON_CUSTOM_SHORTCODE_KEY, $AR_FLUENTCRM_COUPON_CUSTOM_SHORTCODE_PREFIX);
}