/*jshint browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'ko',
        'Magento_Ui/js/modal/confirm',
        'jquery',
        'Magento_Customer/js/customer-data',
        'mage/url',
        'mage/template',
        'jquery/ui',
        'mage/translate'
    ], function (
        Component,
        ko,
        confirm,
        $,
        customerData,
        urlBuilder,
        mageTemplate
    ) {
        'use strict';

        return Component.extend({
            showButton: ko.observable(false),
            paymentToken: ko.observable(null),
            shippingAddress: ko.observable(null),
            billingAddress: ko.observable(null),
            shippingMethod: ko.observable(null),
            defaults: {
                template: 'Magento_InstantPurchase/instant-purchase',
                buttonText: $.mage.__('Instant Purchase'),
                purchaseUrl: urlBuilder.build('instantpurchase/button/placeOrder')
            },
            options: {
                message: $.mage.__('Are you sure you want to place order and pay?'),
                formSelector: '#product_addtocart_form',
                confirmTemplate: '<p class="message"><%- data.message %></p>' +
                                 '<strong>' + $.mage.__('Shipping Address') + ':</strong>' +
                                 '<p><%- data.shippingAddress().summary %></p>' +
                                 '<strong>' + $.mage.__('Billing Address') + ':</strong>' +
                                 '<p><%- data.billingAddress().summary %></p>' +
                                 '<strong>' + $.mage.__('Payment Method') + ':</strong>\n' +
                                 '<p><%- data.paymentToken().summary %></p>' +
                                 '<strong>' + $.mage.__('Shipping Method') + ':</strong>\n' +
                                 '<p><%- data.shippingMethod().summary %></p>'
            },

            /** @inheritdoc */
            initialize: function () {
                var self = this,
                    data = customerData.get('instant-purchase')();

                this._super();
                self.showButton(data.available);
                self.paymentToken(data.paymentToken);
                self.shippingAddress(data.shippingAddress);
                self.billingAddress(data.billingAddress);
                self.shippingMethod(data.shippingMethod);
            },

            /**
             * Confirmation method
             */
            instantPurchase: function () {
                var self = this,
                    form = $(self.options.formSelector),
                    confirmTemplate = mageTemplate(this.options.confirmTemplate);

                if (!(form.validation() && form.validation('isValid'))) {
                    return;
                }

                confirm({
                    title: $.mage.__('Instant Purchase Confirmation'),
                    content: confirmTemplate(
                        {
                            data: {
                                message: self.options.message,
                                paymentToken: self.paymentToken,
                                shippingAddress: self.shippingAddress,
                                billingAddress: self.billingAddress,
                                shippingMethod: self.shippingMethod
                            }
                        }
                    ),
                    actions: {
                        /** @inheritdoc */
                        confirm: function () {
                            $.ajax({
                                url: self.purchaseUrl,
                                data: form.serialize(),
                                type: 'post',
                                dataType: 'json',

                                /** Show loader before send */
                                beforeSend: function () {
                                    $('body').trigger('processStart');
                                }
                            }).always(function () {
                                $('body').trigger('processStop');
                            });
                        }
                    }
                });
            }
        });
    }
);
