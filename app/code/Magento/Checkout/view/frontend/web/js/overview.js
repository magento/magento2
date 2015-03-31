/**
 * @category    checkout multi-shipping review order overview
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
/*global alert*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate"
], function($){
    "use strict";

    $.widget('mage.orderOverview', {
        options: {
            opacity: 0.5, // CSS opacity for the 'Place Order' button when it's clicked and then disabled.
            pleaseWaitLoader: 'span.please-wait', // 'Submitting order information...' Ajax loader.
            placeOrderSubmit: 'button[type="submit"]' // The 'Place Order' button.
        },

        /**
         * Bind a submit handler to the form.
         * @private
         */
        _create: function() {
            this.element.on('submit', $.proxy(this._showLoader, this));
        },

        /**
         * Show the Ajax loader. Disable the submit button (i.e. Place Order).
         * @return {Boolean}
         * @private
         */
        _showLoader: function() {
            if (!this.element.validation('isValid')) {
                return false;
            }
            this.element.find(this.options.pleaseWaitLoader).show().end()
                .find(this.options.placeOrderSubmit).prop('disabled', true).css('opacity', this.options.opacity);
            return true;
        }
    });

    return $.mage.orderOverview;
});