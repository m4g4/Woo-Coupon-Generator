<?php
/**
 * Plugin Name: Woo Coupon Generator
 * Description: Dynamically generates unique WooCommerce coupons for email marketing tools. Supports FluentCRM and MailPoet.
 * Version:     1.0.0
 * Author:      m4g4
 * License:     GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * 
 * Requires at least: 5.6
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

require_once __DIR__ . '/fluent_crm_coupon_generator.php';
require_once __DIR__ . '/mailpoet_coupon_generator.php';
require_once __DIR__ . '/settings.php';

?>