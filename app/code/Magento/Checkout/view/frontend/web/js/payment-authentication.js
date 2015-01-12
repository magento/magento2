/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
    $.widget('mage.paymentAuthentication', {
        options : {
            bodySelector: '[data-container="body"]'
        },

        _create: function () {
            // add a trigger on the body for payment authentication state changes
            this.element.closest(this.options.bodySelector).on("paymentAuthentication", $.proxy(this._paymentmentAthenticationTrigger, this));
        },

        /**
         * This method processes the paymentAuthentication actions.
         */
        _paymentmentAthenticationTrigger: function (event, data) {
            if (data.state === 'start') {
                this.element.hide();
            } else {
                this.element.show();
            }
        }
    });

    return $.mage.paymentAuthentication;
});