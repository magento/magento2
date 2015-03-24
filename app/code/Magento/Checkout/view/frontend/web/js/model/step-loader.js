/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
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
