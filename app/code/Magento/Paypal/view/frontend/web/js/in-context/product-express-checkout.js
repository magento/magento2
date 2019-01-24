/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'uiComponent',
    'Magento_Paypal/js/in-context/express-checkout-smart-buttons'
], function (_, $, Component, checkoutSmartButtons) {
    'use strict';

    return Component.extend({
        defaults: {
            productFormSelector: '#product_addtocart_form',
            validationElements: 'input',
            getQuoteUrl: '',
            formInvalid: false
        },

        initialize: function (config, element) {
            this._super();
            checkoutSmartButtons(this.prepareClientConfig(), element);
        },

        validate: function (actions) {
            this.actions = actions;
        },

        onClick: function () {
            var $form = $(this.productFormSelector);

            $form.submit();
            this.formInvalid = !$form.validation('isValid') ? true : false;
        },

        beforePayment: function (resolve, reject) {
            var promise = $.Deferred();

            if (this.formInvalid) {
                reject();
            } else {
                promise.resolve();
            }

            return promise;
        },

        /**
         * @returns {String}
         */
        getButtonId: function () {
            return this.inContextId;
        },

        /**
         * @returns {String}
         */
        getAgreementId: function () {
            return this.inContextId + '-agreement';
        },

        /**
         * Populate client config with all required data
         *
         * @return {Object}
         */
        prepareClientConfig: function () {
            this.clientConfig.client = {};
            this.clientConfig.client[this.clientConfig.environment] = this.clientConfig.merchantId;
            this.clientConfig.rendererComponent = this;
            this.clientConfig.formKey = $.mage.cookies.get('form_key');
            this.clientConfig.commit = false;

            return this.clientConfig;
        }
    });
});
