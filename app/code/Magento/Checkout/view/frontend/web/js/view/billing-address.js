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
        'jquery',
        'Magento_Ui/js/form/component',
        'Magento_Customer/js/model/customer',
        '../action/select-billing-address'
    ],
    function ($, Component, customer, selectBillingAddress) {
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-address',
                billingAddresses: customer.getBillingAddressList(),
                selectedAddressId: null,
                submitBillingAddress: function(form) {
                    form = $(form);
                    var selectedAddressId = form.find('select[name="billing_address_id"]').val(),
                        useForShipping = form.find('input[name="billing[use_for_shipping]"]:checked').val();
                    selectBillingAddress(selectedAddressId, useForShipping)
                }
            }
        });
    }
);
