/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/sidebar'
], function ($, Component, quote, stepNavigator, sidebarModel) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-information'
        },

        /**
         * @return {Boolean}
         */
        isVisible: function () {
            return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
        },

        /**
         * @return {String}
         */
        getShippingMethodTitle: function () {
            var shippingMethod,
                shippingTitleParts = [];

            shippingMethod = quote.shippingMethod();

            if (typeof shippingMethod['carrier_title'] !== 'undefined' &&
                shippingMethod['carrier_title'].trim().length !== 0) {
                shippingTitleParts.push(shippingMethod['carrier_title']);
            }

            if (typeof shippingMethod['method_title'] !== 'undefined' &&
                shippingMethod['method_title'].trim().length !== 0) {
                shippingTitleParts.push(shippingMethod['method_title']);
            }

            return shippingTitleParts.join(' - ');
        },

        /**
         * Back step.
         */
        back: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping');
        },

        /**
         * Back to shipping method.
         */
        backToShippingMethod: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping', 'opc-shipping_method');
        }
    });
});
