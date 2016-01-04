/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'Magento_Paypal/js/view/payment/method-renderer/paypal-express-abstract',
        'Magento_Paypal/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function (
        $,
        Component,
        setPaymentMethodAction,
        additionalValidators
    ) {

        return Component.extend({

            defaults: {
                template: 'Magento_Paypal/payment/payflow-express-in-context',
                paypalButtonSelector: '#paypal-express-in-context-checkout-main'
            },

            click: function () {
                $(this.paypalButtonSelector).click();
            },

            /**
             * @returns {Boolean}
             */
            continueToPayPal: function () {
                if (additionalValidators.validate()) {

                    this.selectPaymentMethod();

                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            this.click();
                        }.bind(this)
                    );

                    return false;
                }
            }
        });
    }
);
