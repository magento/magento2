/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
    $.widget('mage.checkoutBalance', {
        /**
         * Initialize store credit events
         * @private
         */
        _create: function() {
            this.eventData = {
                price: this.options.balance,
                totalPrice: 0
            };
            this.element.on('change', $.proxy(function(e) {
                if ($(e.target).is(':checked')) {
                    this.eventData.price = -1 * this.options.balance;
                } else {
                    if (this.options.amountSubstracted) {
                        this.eventData.price = this.options.usedAmount;
                        this.options.amountSubstracted = false;
                    } else {
                        this.eventData.price = this.options.balance;
                    }
                }
                this.element.trigger('updateCheckoutPrice', this.eventData);
            }, this));
        }
    });

    return $.mage.checkoutBalance;
});
