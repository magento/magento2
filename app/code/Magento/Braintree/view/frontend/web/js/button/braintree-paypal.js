/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Braintree/js/button/builder',
    'Magento_Braintree/js/button/action/get-data',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/lib/view/utils/dom-observer'
], function ($, builder, actionGetData, customerData, domObserver) {

    return function (config) {

        var button = {
            isEmpty: false,

            isClick: false,

            isAfterClick: false,

            isRendered: false,

            afterCartUpdateAction: function () {
                customerData.get('cart')
                    .subscribe(function () {
                        if (this.isClick) {
                            actionGetData.request().done(button.update.bind(button));
                        }
                    }.bind(this));

                $('#product_addtocart_form').submit();
            },

            afterClickAction: function () {
                var paypalButton = config.containerId + ' .paypal-button';
                $('body').trigger('processStart');

                domObserver.get(paypalButton, function (event) {
                    domObserver.off(paypalButton);
                    if (this.isRendered) {
                        return;
                    }
                    $('body').trigger('processStop');
                    this.isRendered = false;
                    this.isAfterClick = true;
                    $(paypalButton).click();
                }.bind(this));
            },

            update: function (response) {
                this.isEmpty = response.isEmpty;

                config.options.amount = response.isEmpty ? 1 : response.amount;
                config.options.currency = response.isEmpty ? 'USD' : response.currency;

                if (this.isEmpty && this.isClick) {
                    this.afterCartUpdateAction();
                } else if (this.isClick && !this.isEmpty) {
                    this.isRendered = true;
                    this.afterClickAction();
                }

                this.isRendered = false;

                builder.setClientToken(config.clientToken)
                    .setOptions(config.options)
                    .setName('paypal')
                    .setContainer(config.containerId)
                    .setPayment(config.paymentId)
                    .setDetails(config.detailsId)
                    .setFormAction(config.formAction)
                    .build();
            },

            click: function (event) {
                if (!this.isEmpty && this.isAfterClick) {
                    this.isClick = false;
                    this.isAfterClick = false;
                    return;
                }

                this.isClick = true;
                event.preventDefault();
                event.stopPropagation();
                actionGetData.request().done(button.update.bind(button));
            }
        };

        $(config.containerId).on('click', button.click.bind(button));

        actionGetData.when().promise().done(button.update.bind(button));
    };
});
