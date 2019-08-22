/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
    'Magento_Checkout/js/view/payment/default',
    'jquery',
    'mage/validation'
], function (Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_OfflinePayments/payment/purchaseorder-form',
            purchaseOrderNumber: ''
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('purchaseOrderNumber');

            return this;
        },

        /**
         * @return {Object}
         */
        getData: function () {
            return {
                method: this.item.method,
                'po_number': this.purchaseOrderNumber(),
                'additional_data': null
            };
        },

        /**
         * @return {jQuery}
         */
        validate: function () {
            var form = 'form[data-role=purchaseorder-form]';

            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
