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
        '../model/quote',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Customer/js/model/customer'
    ],
    function(Component, quote, selectShippingAddress, customer) {
        customer.load();
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address',
                addresses: customer.getShippingAddressList(),
                selectedAddressId: null,
                selectShippingAddress: function() {
                    selectShippingAddress(this.selectedAddressId);
                }
            }
        });
    }
);
