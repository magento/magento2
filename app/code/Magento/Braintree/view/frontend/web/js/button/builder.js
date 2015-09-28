/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    "braintree",
    'underscore',
    "mage/template"
], function ($, braintree, _, mageTemplate) {
    'use strict';

    // private
    var payment = {
        form: {
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

        prepare: function (allow) {
            var self = this;
            var config = {
                onPaymentMethodReceived: function (payment) {
                    self.form.build(
                        {
                            action: self.formAction,
                            fields: {
                                'payment_method_nonce': payment.nonce,
                                'details': JSON.stringify(payment.details)
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
        setClientToken: function (clientToken) {
            payment.clientToken = clientToken;
            return this;
        },

        setOptions: function (options) {
            payment.options = options;
            return this;
        },

        setName: function (name) {
            payment.name = name;
            return this;
        },

        setContainer: function (containerId) {
            payment.containerId = containerId;
            return this;
        },

        setPayment: function (paymentId) {
            payment.paymentId = paymentId;
            return this;
        },

        setDetails: function (detailsId) {
            payment.detailsId = detailsId;
            return this;
        },

        setFormAction: function (formAction) {
            payment.formAction = formAction;
            return this;
        },

        build: function () {
            $(payment.containerId).empty();
            braintree.setup(payment.clientToken, payment.name, payment.getConfig());
        }
    };
});
