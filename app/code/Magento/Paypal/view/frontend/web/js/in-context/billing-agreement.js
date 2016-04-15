/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    $.widget('mage.billingAgreement', {
        options: {
            invalidateOnCreate: false,
            cancelButtonSelector: '.block-billing-agreements-view button.cancel',
            cancelMessage: '',
            cancelUrl: ''
        },

        /**
         * Initialize billing agreements events
         * @private
         */
        _create: function () {
            if (this.options.invalidateOnCreate) {
                this.invalidate();
            }
            this.element.on('click', $.proxy(function () {
                if (confirm(this.options.cancelMessage)) {
                    this.invalidate();
                    window.location.href = this.options.cancelUrl;
                }

                return false;
            }, this));
        },

        /**
         * clear paypal billing agreement customer data
         * @returns void
         */
        invalidate: function () {
            customerData.invalidate(['paypal-billing-agreement']);
        }
    });

    return $.mage.billingAgreement;
});
