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
        //var root;
        //var shippingAddressTemplate = _.template(template);
        //var object = {
        //    render: function (newRoot) {
        //        root = newRoot || root;
        //        if (customer.isLoggedIn()) {
        //            customer.getShippingAddressList().then(function(addressList) {
        //                root.html(shippingAddressTemplate({addresses: addressList.addresses}));
        //                root.find('#shipping-address-form').on('submit', function (e) {
        //                    e.preventDefault();
        //                    selectShippingAddress(root.find('#shipping-address').val());
        //                });
        //            });
        //        } else {
        //            root.html('Log in to select shipping address');
        //        }
        //    }
        //};
        //quote.setShippingAddress = _.wrap(_.bind(quote.setShippingAddress, quote),
        //    function (func, addressId, shipToSame) {
        //        return func(addressId, shipToSame).done(function() {
        //            if (quote.getShippingAddress()) {
        //                root.hide(1000);
        //            }
        //        });
        //    }
        //);
        //customer.setIsLoggedIn= _.wrap(_.bind(customer.setIsLoggedIn, customer),
        //    function (func, isLoggedIn) {
        //        var result = func (isLoggedIn);
        //        if (root) {
        //            object.render();
        //        }
        //        return result;
        //    }
        //);

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address',
                addresses: customer.getShippingAddressList(),
                selectShippingAddress: function() {
                    //selectShippingAddress(root.find('#shipping-address').val());
                }
            }
        });
    }
);
