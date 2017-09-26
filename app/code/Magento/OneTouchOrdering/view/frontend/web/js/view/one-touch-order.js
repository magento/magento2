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
        'underscore',
        'jquery/ui',
        'mage/translate'
    ], function (
        Component,
        ko,
        confirm,
        $,
        customerData,
        urlBuilder,
        mageTemplate,
        _
    ) {
        'use strict';

        return Component.extend({
            showButton: ko.observable(false),
            currentCard: ko.observable(null),
            currentShipping: ko.observable(null),
            defaults: {
                template: 'Magento_OneTouchOrdering/one-touch-order',
                buttonText: $.mage.__('One Touch Ordering')
            },
            options: {
                message: $.mage.__('Are you sure you want to place order and pay?'),
                formSelector: '#product_addtocart_form',
                addresses: ko.observable([]),
                cards: ko.observable([]),
                selectAddressAvailable: ko.observable(false),
                defaultBilling: ko.observable(0),
                defaultShipping: ko.observable(0),
                confirmTemplate: '<p class="message"><%- data.message %></p>' +
                '<strong>' + $.mage.__('Shipping Address') + ':</strong>' +
                '<p><%- data.shippingAddress %></p>' +
                '<strong>' + $.mage.__('Billing Address') + ':</strong>' +
                '<p><%- data.billingAddress %></p>' +
                '<strong>' + $.mage.__('Credit Card') + ':</strong>\n' +
                '<p><%- data.creditCard %></p>'
            },

            /** @inheritdoc */
            initialize: function () {
                var self = this;

                this._super();
                $.get(urlBuilder.build('onetouchorder/button/available')).done(function (data) {
                    if (typeof data.available !== 'undefined') {
                        self.showButton(data.available);
                        self.options.cards(data.cards);
                        self.currentCard(_.first(data.cards).card);
                        self.options.addresses(data.addresses);
                        self.options.defaultShipping(data.defaultShipping);
                        self.options.selectAddressAvailable(data.selectAddressAvailable);
                        self.options.defaultBilling(
                            _.find(data.addresses, function (obj) {
                                return obj.id === data.defaultBilling;
                            }).address
                        );
                        self.currentShipping(
                            _.find(data.addresses, function (obj) {
                                return obj.id === data.defaultShipping;
                            }).address
                        );
                    }
                });
            },

            /**
             * Change shipping method
             */
            changeShipping: function (object, event) {
                this.currentShipping(
                    _.find(this.options.addresses(), function (obj) {
                        return obj.id === event.target.value;
                    }).address
                );
            },

            /**
             * Change credit card method
             */
            changeCc: function (object, event) {
                this.currentCard(
                    _.find(this.options.cards(), function (obj) {
                        return obj.id === event.target.value;
                    }).card
                );
            },

            /**
             * Confirmation method
             */
            oneTouchOrder: function () {
                var self = this,
                    form = $(self.options.formSelector),
                    confirmTemplate = mageTemplate(this.options.confirmTemplate);

                if (!(form.validation() && form.validation('isValid'))) {
                    return;
                }
                confirm({
                    content: confirmTemplate(
                        {
                            data: {
                                message: self.options.message,
                                shippingAddress: self.currentShipping(),
                                billingAddress: self.options.defaultBilling(),
                                creditCard: self.currentCard()
                            }
                        }
                    ),
                    actions: {
                        /** @inheritdoc */
                        confirm: function () {
                            $.ajax({
                                url: urlBuilder.build('onetouchorder/button/placeOrder'),
                                data: form.serialize(),
                                type: 'post',
                                dataType: 'json',

                                /** Show loader before send */
                                beforeSend: function () {
                                    $('body').trigger('processStart');
                                }
                            }).done(function () {
                                $('body').trigger('processStop');
                            });
                        }
                    }
                });
            }
        });
    }
);
