/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/mage"
], function($){
    "use strict";

    $.widget('mage.paypalCheckout', {
        /**
         * Initialize store credit events
         * @private
         */
        _create: function () {
            this.element.on('click', '[data-action="checkout-form-submit"]', $.proxy(function (e) {
                var returnUrl = $(e.target).data('checkout-url');
                if (this.options.confirmUrl && this.options.confirmMessage) {
                    if (window.confirm(this.options.confirmMessage)) {
                        returnUrl = this.options.confirmUrl;
                    }
                }
                if (this.options.isCatalogProduct) {
                    // find the form from which the button was clicked
                    var form  = $(this.options.shortcutContainerClass).closest("form");
                    $(form).find(this.options.paypalCheckoutSelector).val(returnUrl);
                    $(form).submit();
                } else {
                    $.mage.redirect(returnUrl);
                }
            }, this));
        }
    });
});