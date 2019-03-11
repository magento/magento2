/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'mage/translate'
], function (
    $,
    _,
    Component,
    ko,
    quote,
    stepNavigator,
    paymentService,
    methodConverter,
    getPaymentInformation,
    checkoutDataResolver,
    $t
) {
    'use strict';

    /** Set payment methods to collection */
    paymentService.setPaymentMethods(methodConverter(window.checkoutConfig.paymentMethods));

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/payment',
            activeMethod: ''
        },
        isVisible: ko.observable(quote.isVirtual()),
        quoteIsVirtual: quote.isVirtual(),
        isPaymentMethodsAvailable: ko.computed(function () {
            return paymentService.getAvailablePaymentMethods().length > 0;
        }),

        /** @inheritdoc */
        initialize: function () {
            this._super();
            checkoutDataResolver.resolvePaymentMethod();
            stepNavigator.registerStep(
                'payment',
                null,
                $t('Review & Payments'),
                this.isVisible,
                _.bind(this.navigate, this),
                20
            );

            return this;
        },

        /**
         * Navigate method.
         */
        navigate: function () {
            var self = this;

            if (!self.hasShippingAddress()) {
                this.isVisible(false);
                stepNavigator.setHash('shipping');
            } else {
                getPaymentInformation().done(function () {
                    self.isVisible(true);
                });
            }
        },

        /**
         * @return {*}
         */
        hasShippingAddress: function () {
            var shippingAddress = quote.shippingAddress(),
                isShippingAddress = false;

            if (typeof quote.guestEmail == 'undefined' &&
                typeof shippingAddress.email == 'undefined' ||
                typeof shippingAddress.firstname == 'undefined' ||
                typeof shippingAddress.lastname == 'undefined' ||
                typeof shippingAddress.countryId == 'undefined' ||
                typeof shippingAddress.city == 'undefined' ||
                typeof shippingAddress.telephone == 'undefined' ||
                typeof shippingAddress.postcode == 'undefined'
            ) {
                isShippingAddress = false;
            } else if (
                    quote.guestEmail.length === 0 &&
                    shippingAddress.email.length === 0 ||
                    shippingAddress.firstname.length === 0 ||
                    shippingAddress.lastname.length === 0 ||
                    shippingAddress.countryId.length === 0 ||
                    shippingAddress.city.length === 0 ||
                    shippingAddress.telephone.length === 0 ||
                    shippingAddress.postcode.length === 0 ||
                    shippingAddress.street.length === 0
                ) {
                isShippingAddress = false;
            } else {
                isShippingAddress = true;
            }

            return isShippingAddress;
        },

        /**
         * @return {*}
         */
        getFormKey: function () {
            return window.checkoutConfig.formKey;
        }
    });
});
