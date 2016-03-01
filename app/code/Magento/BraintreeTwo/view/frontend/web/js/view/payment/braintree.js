/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'uiRegistry',
        'Magento_BraintreeTwo/js/view/payment/adapter',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        Registry,
        Braintree,
        rendererList
    ) {
        'use strict';

        var config = window.checkoutConfig.payment,
            braintreeType = 'braintree',
            payPalType = 'braintree_paypal',
            path = 'checkout.steps.billing-step.payment.payments-list.',
            components = [];

        if (config[braintreeType].isActive) {
            components.push(path + braintreeType);
            rendererList.push(
                {
                    type: braintreeType,
                    component: 'Magento_BraintreeTwo/js/view/payment/method-renderer/hosted-fields'
                }
            );
        }

        if (config[payPalType].isActive) {
            rendererList.push(
                {
                    type: payPalType,
                    component: 'Magento_BraintreeTwo/js/view/payment/method-renderer/paypal'
                }
            );
        }

        // setup Braintree SDK with merged configuration from all related components
        if (components.length) {
            Registry.get(components, function () {
                Braintree.setup();
            });
        }

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
