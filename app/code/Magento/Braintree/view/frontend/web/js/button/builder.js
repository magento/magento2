/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'braintree',
    'underscore',
    'mage/template'
], function ($, braintree, _, mageTemplate) {
    'use strict';

    // private
    var payment = {
        form: {

            /**
             * @param {*} formData
             * @returns {jQuery}
             */
            build: function (formData) {
                var formTmpl = mageTemplate('<form action="<%= data.action %>"' +
                    ' method="POST" hidden enctype="multipart/form-data">' +
                    '<% _.each(data.fields, function(val, key){ %>' +
                    '<input value=\'<%= val %>\' name="<%= key %>" type="hidden">' +
                    '<% }); %>' +
                    '</form>');

                return $(formTmpl({
                    data: {
                        action: formData.action,
                        fields: formData.fields
                    }
                })).appendTo($('[data-container="body"]'));
            }
        },

        /**
         * @param {*} allow
         * @returns {*}
         */
        prepare: function (allow) {
            var self = this,
                config = {

                    /**
                     * @param {*} paymentResult
                     * @returns
                     */
                    onPaymentMethodReceived: function (paymentResult) {
                        self.form.build(
                            {
                                action: self.formAction,
                                fields: {
                                    'payment_method_nonce': paymentResult.nonce,
                                    'details': JSON.stringify(paymentResult.details)
                                }
                            }
                        ).submit();
                    }
                };

            _.each(this.options, function (option, name) {
                if (option !== null && _.indexOf(allow, name) !== -1) {
                    config[name] = option;
                }
            });

            return config;
        },

        /**
         * @returns {*}
         */
        getConfig: function () {
            return this.prepare([
                'merchantName',
                'locale',
                'enableBillingAddress',
                'currency',
                'amount',
                'container',
                'singleUse',
                'enableShippingAddress'
            ]);
        }
    };

    // public
    return {

        /**
         * @param {String} clientToken
         * @returns {*}
         */
        setClientToken: function (clientToken) {
            payment.clientToken = clientToken;

            return this;
        },

        /**
         * @param {*} options
         * @returns {*}
         */
        setOptions: function (options) {
            payment.options = options;

            return this;
        },

        /**
         * @param {String} name
         * @returns {*}
         */
        setName: function (name) {
            payment.name = name;

            return this;
        },

        /**
         * @param {String} containerId
         * @returns {*}
         */
        setContainer: function (containerId) {
            payment.containerId = containerId;

            return this;
        },

        /**
         * @param {String} paymentId
         * @returns {*}
         */
        setPayment: function (paymentId) {
            payment.paymentId = paymentId;

            return this;
        },

        /**
         * @param {String} detailsId
         * @returns {*}
         */
        setDetails: function (detailsId) {
            payment.detailsId = detailsId;

            return this;
        },

        /**
         * @param {String} formAction
         * @returns {*}
         */
        setFormAction: function (formAction) {
            payment.formAction = formAction;

            return this;
        },

        /**
         * @returns
         */
        build: function () {
            $(payment.containerId).empty();
            braintree.setup(payment.clientToken, payment.name, payment.getConfig());
        }
    };
});
