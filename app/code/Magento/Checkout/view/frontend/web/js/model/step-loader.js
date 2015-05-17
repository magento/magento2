/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['jquery'], function($) {
    return {
        registerLoader: function() {
            $(document).bind('ajaxSend', function() {
                $('#checkout').trigger("processStart");
            });

            $(document).bind('ajaxComplete', function() {
                $('#checkout').trigger("processStop");
            });

            $(document).bind('ajaxError', function() {
                $('#checkout').trigger("processStop");
            });
        }
    };
});
