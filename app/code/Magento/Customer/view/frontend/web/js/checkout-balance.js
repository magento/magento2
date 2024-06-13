/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.checkoutBalance', {
        /**
         * Initialize store credit events
         * @private
         */
        _create: function () {
            this.eventData = {
                price: this.options.balance,
                totalPrice: 0
            };
            this.element.on('change', $.proxy(function (e) {
                if ($(e.target).is(':checked')) {
                    this.eventData.price = -1 * this.options.balance;
                } else {
                    if (this.options.amountSubstracted) { //eslint-disable-line no-lonely-if
                        this.eventData.price = parseFloat(this.options.usedAmount);
                        this.options.amountSubstracted = false;
                    } else {
                        this.eventData.price = parseFloat(this.options.balance);
                    }
                }
                this.element.trigger('updateCheckoutPrice', this.eventData);
            }, this));
        }
    });

    return $.mage.checkoutBalance;
});
