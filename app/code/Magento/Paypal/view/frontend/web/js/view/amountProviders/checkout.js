/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiElement',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'domReady!'
], function (
    $,
    ko,
    Component,
    registry,
    quote
) {
    'use strict';

    return Component.extend({
        defaults: {
            amount: null
        },

        /**
         * Initialize
         *
         * @returns {*}
         */
        initialize: function () {
            this._super();

            this.updateAmount();

            return this;
        },

        /**
         * Update amount
         */
        updateAmount: function () {
            var payLater = registry.get(this.parentName);

            quote.totals.subscribe(function (newValue) {
                payLater.amount(newValue['base_grand_total']);
            });
        }
    });
});
