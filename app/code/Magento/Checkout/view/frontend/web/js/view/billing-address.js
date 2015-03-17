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
        'underscore',
        'text!./templates/billing-address.html',
        '../model/order',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Customer/js/model/customer'
    ],
    function(_, template, order, selectBillingAddress, customer) {
        var root;
        var template = _.template(template);
        var object = {
            render: function (newRoot) {
                root = newRoot || root;
                if (customer.isLoggedIn()) {
                    customer.getBillingAddressList().then(function(addressList) {
                        root.html(template({addresses: addressList.addresses}));
                        root.find('#billing-address-form').on('submit', function (e) {
                            e.preventDefault();
                            selectBillingAddress(
                                root.find('#billing-address').val(),
                                root.find('#use_for_shipping').is(':checked')
                            );
                        });
                    });
                } else {
                    root.html('Log in to select billing address');
                }
            }
        };
        order.setBillingAddress = _.wrap(_.bind(order.setBillingAddress, order),
            function (func, addressId, shipToSame) {
                return func(addressId, shipToSame).done(function() {
                    if (order.getBillingAddress()) {
                        root.hide(1000);
                    }
                });
            }
        );
        customer.setIsLoggedIn= _.wrap(_.bind(customer.setIsLoggedIn, customer),
            function (func, isLoggedIn) {
                var result = func (isLoggedIn);
                if (root) {
                    object.render();
                }
                return result;
            }
        );
        return object;
    }
);
