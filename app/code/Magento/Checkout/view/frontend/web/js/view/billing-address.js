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
        'Magento_Checkout/js/model/step-navigator'
    ],
    function (Component, customer, selectBillingAddress, navigator) {
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-address',
                billingAddresses: customer.getBillingAddressList(),
                selectedBillingAddressId: "1",
                isVisible: navigator.isStepVisible('billingAddress'),
                useForShipping: "1",
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
