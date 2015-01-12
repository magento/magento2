/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define(["jquery"], function($){
    "use strict";
    $(document).on("login",function() {
        $("[data-captcha='guest_checkout'], [data-captcha='register_during_checkout']").hide();
        $("[role='guest_checkout'], [role='register_during_checkout']").hide();
        var type = ($("#login\\:guest").is(':checked')) ? 'guest_checkout' : 'register_during_checkout';
        $("[role='" + type + "'], [data-captcha='" + type + "']").show();
    }).on('billingSave', function() {
            $(".captcha-reload:visible").trigger("click");
        });
});