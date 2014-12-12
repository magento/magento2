/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "Magento_Checkout/js/opc-order-review",
    "jquery/ui"
], function($, opcOrderReview){
    "use strict";

    $.widget('mage.opcheckoutPaypalIframe', opcOrderReview, {
        options: {
            review: {
                submitContainer: '#checkout-review-submit'
            }
        },

        _create: function() {
            var events = {};
            events['contentUpdated' + this.options.review.container] = function() {
                var paypalIframe = this.element.find(this.options.review.container)
                    .find('[data-container="paypal-iframe"]');
                if (paypalIframe.length) {
                    paypalIframe.show();
                    $(this.options.review.submitContainer).hide();
                }
            };
            this._on(events);
        }
    });

    return $.mage.opcheckoutPaypalIframe;
});