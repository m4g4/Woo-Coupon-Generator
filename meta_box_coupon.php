<?php
global $AR_ONE_TIME_COUPON_ENABLED, $AR_ONE_TIME_COUPON_PREFIX;

// Security check
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<input type="hidden" name="one_time_coupon_nonce" id="one_time_coupon_nonce" value="<?php echo wp_create_nonce( 'one_time_coupon' ); ?>" />

<div id="ar_coupon_panel">
	Use this coupon as a template for one time coupons: <br />
	<input type="checkbox" name="<?php echo $AR_ONE_TIME_COUPON_ENABLED; ?>" id="<?php echo $AR_ONE_TIME_COUPON_ENABLED; ?>" value="true" <?php echo $one_time_coupon_enabled; ?> /><br /><br />
	
    <div id="ar_coupon_panel_options" style="display: none;">
        <?php if (ar_is_mailpoet_active()) { ?>
            Mailpoet Shortcode:<br />
	        <input type="text" id="ar_mailpoet_coupon_shortcode" value="[<?php echo ar_mailpoet_coupon_gen_shortcode()?><?php echo $post_title?>]" disabled/> <button type="button" id="ar_mailpoet_coupon_copy_to_clipboard" style="cursor: pointer"><i class="fa fa-copy" title="Copy to clipboard"></i></button><br /><br />
        <?php }?>
        <?php if (ar_is_fluentcrm_active()) { ?>
            FluentCRM Smartcode:<br />
	        <input type="text" id="ar_fluentcrm_coupon_shortcode" value="{{<?php echo ar_fluentcrm_coupon_gen_smartcode()?>:<?php echo $post_title?>}}" disabled/> <button type="button" id="ar_fluentcrm_coupon_copy_to_clipboard" style="cursor: pointer"><i class="fa fa-copy" title="Copy to clipboard"></i></button><br /><br />
        <?php }?>
        New coupon prefix:<br />
	    <input type="text" name="<?php echo $AR_ONE_TIME_COUPON_PREFIX; ?>" id="<?php echo $AR_ONE_TIME_COUPON_PREFIX; ?>" value="<?php echo $one_time_coupon_prefix; ?>"/><br /><br />
        Example: <span id="ar_coupon_example"></span><br />
    </div>
</div>