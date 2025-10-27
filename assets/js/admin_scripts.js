(function($) {
    function change_coupon_shortcode(inputId, code, codeId, divider, prefix, suffix) {
        var input = document.getElementById(inputId);
        if (!input)
            return;

        input.value = `${prefix}${code}${divider}${codeId}${suffix}`;
    }

    // Post Title Detection
    var $titleInput = $('#title');
    if ($titleInput.length) {
        var previousTitle = $titleInput.val();

        $titleInput.on('input change', function() {
            var currentTitle = $titleInput.val();
            if (currentTitle !== previousTitle) {
                change_coupon_shortcode('ar_mailpoet_coupon_shortcode', woo_copoun_generator.mailpoet_shortcode, currentTitle, '', '[', ']');
                change_coupon_shortcode('ar_fluentcrm_coupon_shortcode', woo_copoun_generator.fluentcrm_smartcode, currentTitle, ':', '{{', '}}');
                previousTitle = currentTitle;
            }
        });

    } else {
        console.warn('Woo Coupon Generator: Post title input not found.');
    }

    // Coupon Panel Functionality
    document.addEventListener("DOMContentLoaded", function() {
        var enablement = document.getElementById(woo_copoun_generator.coupon_enabled_id);
        var prefix = document.getElementById(woo_copoun_generator.coupon_prefix_id);
        var fields = document.getElementById('ar_coupon_panel_options');

        if (!enablement || !prefix || !fields) {
            console.warn('Coupon panel elements not found:', {
                enablement: woo_copoun_generator.coupon_enabled_id,
                prefix: woo_copoun_generator.coupon_prefix_id,
                fields: 'ar_coupon_panel_options'
            });
            return;
        }

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

        function attach_copy_click_handler(copyButtonId, inputId) {
            var copyButton = document.getElementById(copyButtonId);
            if (!copyButton)
                return;

            copyButton.addEventListener("click", function() {
                var copyIcon = document.querySelector("#" +copyButtonId+ " > i");
                var copyText = document.getElementById(inputId);
    
                if (!copyIcon || !copyText) {
                    console.warn('Copy elements not found.');
                    return;
                }
            
                copyText.select();
                copyText.setSelectionRange(0, 99999);
            
                function showCheckmark() {
                    copyIcon.classList.remove("fa-copy");
                    copyIcon.classList.add("fa-check");
                    setTimeout(() => {
                        copyIcon.classList.remove("fa-check");
                        copyIcon.classList.add("fa-copy");
                    }, 2000);
                }
            
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(copyText.value).then(function() {
                        showCheckmark();
                    }).catch(function(err) {
                        document.execCommand("copy");
                        showCheckmark();
                    });
                } else {
                    document.execCommand("copy");
                    showCheckmark();
                }
            });
        }

        attach_copy_click_handler('ar_mailpoet_coupon_copy_to_clipboard', 'ar_mailpoet_coupon_shortcode');
        attach_copy_click_handler('ar_fluentcrm_coupon_copy_to_clipboard', 'ar_fluentcrm_coupon_shortcode');
    });
})(jQuery);