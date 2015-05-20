/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        "jquery",
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        '../model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'mage/translate'
    ],
    function($, Component, ko, customer, quote, navigator) {
        'use strict';
        var stepName = 'shippingAddress';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address',
                visible: true,
                formVisible: customer.getShippingAddressList().length === 0
            },
            stepNumber: navigator.getStepNumber(stepName),
            isVisible: navigator.isStepVisible(stepName),
            isCustomerLoggedIn: customer.isLoggedIn(),

            stepClassAttributes: function() {
                return navigator.getStepClassAttributes(stepName);
            },

            /** Initialize observable properties */
            initObservable: function () {
                this._super()
                    .observe('visible');
                return this;
            },

            /** Check if component is active */
            isActive: function() {
                if (quote.isVirtual()) {
                    navigator.setStepEnabled(stepName, false);
                }
                return !quote.isVirtual();
            },

            /** Navigate to current step */
            navigateToCurrentStep: function() {
                if (!navigator.isStepVisible(stepName)()) {
                    navigator.goToStep(stepName);
                }
            }
        });
    }
);
