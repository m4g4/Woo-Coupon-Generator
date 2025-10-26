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
        Mailpoet Shortcode:<br />
	    <input type="text" id="ar_coupon_shortcode" value="[<?php echo ar_mailpoet_coupon_gen_smart_code()?><?php echo $post_title?>]" disabled/> <button type="button" id="ar_coupon_copy_to_clipboard"><i class="fa fa-copy" title="Copy to clipboard"></i></button><br /><br />

        FluentCRM Smartcode:<br />
	    <input type="text" id="ar_coupon_shortcode" value="{{<?php echo ar_fluentcrm_coupon_gen_smart_code()?>:<?php echo $post_title?>}}" disabled/> <button type="button" id="ar_coupon_copy_to_clipboard"><i class="fa fa-copy" title="Copy to clipboard"></i></button><br /><br />

        New coupon prefix:<br />
	    <input type="text" name="<?php echo $AR_ONE_TIME_COUPON_PREFIX; ?>" id="<?php echo $AR_ONE_TIME_COUPON_PREFIX; ?>" value="<?php echo $one_time_coupon_prefix; ?>"/><br /><br />
        Example: <span id="ar_coupon_example"></span><br />
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var enablement = document.getElementById('<?php echo $AR_ONE_TIME_COUPON_ENABLED; ?>');
    var prefix = document.getElementById('<?php echo $AR_ONE_TIME_COUPON_PREFIX; ?>');
    var fields = document.getElementById('ar_coupon_panel_options');

    function toggleOtherFields() {
        fields.style.display = enablement.checked ? 'block' : 'none';
    }

    toggleOtherFields();

    enablement.addEventListener('change', toggleOtherFields);

    function change_example() {
        document.getElementById("ar_coupon_example").textContent = prefix.value + "123456";
    }
    prefix.addEventListener('input', change_example);

    change_example();

    document.getElementById("ar_coupon_copy_to_clipboard").addEventListener("click", function() {
            var copyIcon = document.querySelector("#ar_coupon_copy_to_clipboard > i");
            var copyText = document.getElementById("ar_coupon_shortcode");

            // Select text
            copyText.select();
            copyText.setSelectionRange(0, 99999); // for mobile

            function showCheckmark() {
                copyIcon.classList.remove("fa-copy");
                copyIcon.classList.add("fa-check");

                // Revert back after 2 seconds
                setTimeout(() => {
                    copyIcon.classList.remove("fa-check");
                    copyIcon.classList.add("fa-copy");
                }, 2000);
            }

            // Try modern clipboard API first
            if (navigator.clipboard) {
                navigator.clipboard.writeText(copyText.value).then(function() {
                    showCheckmark();
                }).catch(function(err) {
                    // Fallback
                    document.execCommand("copy");
                    showCheckmark();
                });
            } else {
                document.execCommand("copy");
                showCheckmark();
            }
        });
});
</script>