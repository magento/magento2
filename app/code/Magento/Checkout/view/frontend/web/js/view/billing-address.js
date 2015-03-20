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
        'Magento_Customer/js/model/customer',
        '../action/select-billing-address',
        '../model/quote'
    ],
    function (Component, customer, selectBillingAddress, quote) {
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-address',
                billingAddresses: customer.getBillingAddressList(),
                selectedBillingAddressId: "1",
                isLoggedIn: customer.isLoggedIn(),
                quoteHasBillingAddress: quote.hasBillingAddress(),
                useForShipping: "1",
                submitBillingAddress: function() {
                    selectBillingAddress(this.selectedBillingAddressId, this.useForShipping);
                }
            }
        });
    }
);
