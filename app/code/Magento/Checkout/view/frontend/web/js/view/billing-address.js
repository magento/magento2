/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
    [
        'Magento_Ui/js/form/component',
        'Magento_Customer/js/model/customer',
        '../action/select-billing-address',
        'Magento_Checkout/js/model/step-navigator',
        '../model/quote'
    ],
    function (Component, customer, selectBillingAddress, navigator, quote) {
        var stepName = 'billingAddress';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-address',
                stepNumber: function(){
                    return navigator.getStepNumber(stepName);
                },
                billingAddresses: customer.getBillingAddressList(),
                selectedBillingAddressId: "1",
                isVisible: navigator.isStepVisible(stepName),
                useForShipping: "1",
                quoteIsVirtual: quote.isVirtual(),
                billingAddressesOptionsText: function (item) {
                    return item.getFullAddress();
                },
                submitBillingAddress: function () {
                    selectBillingAddress(this.selectedBillingAddressId, this.useForShipping);
                }
            }
        });
    }
);
