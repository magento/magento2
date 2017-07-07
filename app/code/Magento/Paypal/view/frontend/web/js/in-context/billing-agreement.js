/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Customer/js/customer-data'
], function ($, confirm, customerData) {
    'use strict';

    $.widget('mage.billingAgreement', {
        options: {
            invalidateOnLoad: false,
            cancelButtonSelector: '.block-billing-agreements-view button.cancel',
            cancelMessage: '',
            cancelUrl: ''
        },

        /**
         * Initialize billing agreements events
         * @private
         */
        _create: function () {
            var self = this;

            if (this.options.invalidateOnLoad) {
                this.invalidate();
            }
            $(this.options.cancelButtonSelector).on('click', function () {
                confirm({
                    content: self.options.cancelMessage,
                    actions: {
                        /**
                         * 'Confirm' action handler.
                         */
                        confirm: function () {
                            self.invalidate();
                            window.location.href = self.options.cancelUrl;
                        }
                    }
                });

                return false;
            });
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
