/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'uiComponent',
    'Magento_Paypal/js/in-context/express-checkout-wrapper',
    'Magento_Customer/js/customer-data'
], function (_, $, Component, Wrapper, customerData) {
    'use strict';

    return Component.extend(Wrapper).extend({
        defaults: {
            productFormSelector: '#product_addtocart_form',
            declinePayment: false,
            formInvalid: false
        },

        /** @inheritdoc */
        initialize: function (config, element) {
            var cart = customerData.get('cart'),
                customer = customerData.get('customer');

            this._super();
            this.renderPayPalButtons(element);
            this.declinePayment = !customer().firstname && !cart().isGuestCheckoutAllowed;

            return this;
        },

        /** @inheritdoc */
        onClick: function () {
            var $form = $(this.productFormSelector);

            if (!this.declinePayment) {
                $form.submit();
                this.formInvalid = !$form.validation('isValid');
            }
        },

        /** @inheritdoc */
        beforePayment: function (resolve, reject) {
            var promise = $.Deferred();

            if (this.declinePayment) {
                this.addError(this.signInMessage, 'warning');
                reject();
            } else if (this.formInvalid) {
                reject();
            } else {
                $(document).on('ajax:addToCart', function (e, data) {
                    if (_.isEmpty(data.response)) {
                        return promise.resolve();
                    }

                    return reject();
                });
                $(document).on('ajax:addToCart:error', reject);
            }

            return promise;
        },

        /** @inheritdoc */
        prepareClientConfig: function () {
            this._super();
            this.clientConfig.quoteId = '';
            this.clientConfig.customerId = '';
            this.clientConfig.commit = false;

            return this.clientConfig;
        }
    });
});
