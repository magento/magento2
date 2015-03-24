/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Ui/js/form/component',
        'ko',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Customer/js/model/customer',
        '../model/quote',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function(Component, ko, selectShippingAddress, customer, quote, navigator) {
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address',
                addresses: customer.getShippingAddressList(),
                selectedAddressId: ko.observable(null),
                sameAsBilling: ko.observable(null),
                isVisible: navigator.isShippingAddressVisible(),
                quoteHasBillingAddress: quote.getBillingAddress(),
                selectShippingAddress: function() {
                    selectShippingAddress(this.selectedAddressId(), this.sameAsBilling());
                },
                sameAsBillingClick: function() {
                    if (this.sameAsBilling()) {
                        var billingAddress = quote.getBillingAddress();
                        this.selectedAddressId(billingAddress().customerAddressId);
                    }
                    return true;
                },
                onAddressChange: function() {
                    var billingAddress = quote.getBillingAddress();
                    if (this.selectedAddressId() != billingAddress().customerAddressId) {
                        this.sameAsBilling(false);
                    }
                },
                // Checkout step navigation
                backToBilling: function() {
                    navigator.setCurrent('shippingAddress').goBack();
                }
            }
        });
    }
);
