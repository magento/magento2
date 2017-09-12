/*jshint browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'ko',
        'Magento_Ui/js/modal/confirm',
        'jquery',
        'mage/url',
        'jquery/ui',
        'mage/translate'
    ], function (
        Component,
        ko,
        confirm,
        $,
        urlBuilder
    ) {
        'use strict';

        return Component.extend({
            showButton: ko.observable(false),
            defaults: {
                template: 'Magento_OneTouchOrdering/one-touch-order',
                buttonText: $.mage.__('One Touch Ordering')
            },
            options: {
                message: $.mage.__('Are you sure you want to place order and pay?'),
                formSelector: '#product_addtocart_form'
            },

            /** @inheritdoc */
            initialize: function () {
                var self = this;

                this._super();
                $.get(urlBuilder.build('onetouchorder/button/available')).done(function (data) {
                    if (typeof data.available !== 'undefined') {
                        self.showButton(data.available);
                    }
                });
            },

            /**
             * Confirmation method
             */
            oneTouchOrder: function () {
                var self = this,
                    form = $(self.options.formSelector);

                if (!(form.validation() && form.validation('isValid'))) {
                    return;
                }

                confirm({
                    content: self.options.message,
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
