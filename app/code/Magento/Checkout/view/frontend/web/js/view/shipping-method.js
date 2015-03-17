/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
/*
define(
    [
        'underscore',
        'text!./templates/shipping-method.html',
        '../model/quote',
        '../model/shipping-service',
        '../action/set-shipping-method'
    ],
    function(_, template, quote, shippingService, setShippingMethod) {
        var root;
        template = _.template(template);
        quote.setBillingAddress = _.wrap(_.bind(quote.setBillingAddress, quote), function(func, addressId, shipToSame) {
            return func(addressId, shipToSame).done(function() {
                view.render();
            });
        });
        var view = {
            render: function (newRoot) {
                root = newRoot || root;
                if (quote.getBillingAddress()) {
                    shippingService.getAvailableShippingMethods(quote).then(function(methods) {
                        root.html(template({'shippingRateGroups': methods}));
                    });
                    root.find('#shipping-method-form').on('submit', function (e) {
                        e.preventDefault();
                        setShippingMethod(root.find('#shipping_method').val());
                    });
                } else {
                    root.html('<h2>Shipping Method</h2>');
                }
            }
        };
        return view;
    }
);
*/


define(['Magento_Ui/js/form/component'],
    function (Component) {
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-method'
            }
        });
    }
);
