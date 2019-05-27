/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
    'Magento_Braintree/js/view/payment/method-renderer/hosted-fields',
    'Magento_Braintree/js/validator',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/set-payment-information',
    'Magento_Checkout/js/model/payment/additional-validators'
], function (
    $,
    Component,
    validator,
    messageList,
    $t,
    fullScreenLoader,
    setPaymentInformationAction,
    additionalValidators
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Braintree/payment/multishipping/form'
        },

        /**
         * Get list of available CC types
         *
         * @returns {Object}
         */
        getCcAvailableTypes: function () {
            var availableTypes = validator.getAvailableCardTypes(),
                billingCountryId;

            billingCountryId = $('#multishipping_billing_country_id').val();

            if (billingCountryId && validator.getCountrySpecificCardTypes(billingCountryId)) {
                return validator.collectTypes(
                    availableTypes, validator.getCountrySpecificCardTypes(billingCountryId)
                );
            }

            return availableTypes;
        },

        /**
         * @override
         */
        placeOrder: function () {
            var self = this;

            this.validatorManager.validate(self, function () {
                return self.setPaymentInformation();
            });
        },

        /**
         * @override
         */
        setPaymentInformation: function () {
            if (additionalValidators.validate()) {

                fullScreenLoader.startLoader();

                $.when(
                    setPaymentInformationAction(
                        this.messageContainer,
                        this.getData()
                    )
                ).done(this.done.bind(this))
                    .fail(this.fail.bind(this));
            }
        },

        /**
         * {Function}
         */
        fail: function () {
            fullScreenLoader.stopLoader();

            return this;
        },

        /**
         * {Function}
         */
        done: function () {
            fullScreenLoader.stopLoader();
            $('#multishipping-billing-form').submit();

            return this;
        }
    });
});
