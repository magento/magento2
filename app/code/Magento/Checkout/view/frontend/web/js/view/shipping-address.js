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
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Customer/js/model/customer',
        '../model/quote'
    ],
    function(Component, selectShippingAddress, customer, quote) {
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address',
                addresses: customer.getShippingAddressList(),
                selectedAddressId: null,
                sameAsBilling: null,
                quoteHasShippingAddress: quote.hasShippingAddress(),
                selectShippingAddress: function() {
                    selectShippingAddress(this.selectedAddressId, this.sameAsBilling);
                }
            }
        });
    }
);
