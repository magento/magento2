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
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/place-order'
    ],
    function (ko, Component, quote, orderAction) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review/submit',
                displayArea: 'submit',
                submitLabel: ''
            },

            paymentMethod: quote.getPaymentMethod(),

            initObservable: function () {
                this._super()
                    .observe('submitLabel');
                return this;
            },

            getLabel:  function() {
                var self = this;
                var method = _.find(this._getPaymentComponent().elems(), function(elem) {
                    return elem.index == self.paymentMethod();
                });

                if (method && method.hostedMethod && method.hostedMethod == true) {
                    this.submitLabel('Continue');
                } else {
                    this.submitLabel('Place Order');
                }

                return this.submitLabel();
            },

            _getPaymentComponent: function() {
                var componentCheckout = this.containers[0].containers[0];
                return _.find(componentCheckout.elems(), function(step) {
                    return step.index == 'payment';
                });
            },

            placeOrder: function() {
                orderAction();
            }
        });
    }
);
