/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Braintree/js/button/builder',
    'Magento_Braintree/js/button/action/get-data',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'mageUtils'
], function ($, builder, actionGetData, customerData, domObserver, utils) {
    'use strict';

    /**
     * Ensure that several widget initializations that share common template
     * will provide uniqueIds
     *
     * @param {Object} config
     * @param {HTMLElement} element
     */
    var ensureUniqueIds = function (config, element) {
        var id = utils.uniqueid();

        config.containerId += id;
        config.options.container += id;
        element.id += id;
        config.detailsId += id;
        config.paymentId += id;
    };

    return function (config, element) {
        var button = {
            isClick: false,

            isAfterClick: false,

            isRendered: false,

            /**
             * @returns
             */
            afterClickAction: function () {
                var paypalButton = config.containerId + ' .paypal-button';

                $('body').trigger('processStart');

                domObserver.get(paypalButton, function () {
                    domObserver.off(paypalButton);

                    if (this.isRendered) {
                        this.isRendered = false;

                        return;
                    }
                    $('body').trigger('processStop');
                    this.isAfterClick = true;
                    $(paypalButton).click();
                }.bind(this));
            },

            /**
             * @param {*} response
             * @returns
             */
            update: function (response) {
                config.options.amount = response.isEmpty ? 1 : response.amount;
                config.options.currency = response.isEmpty ? 'USD' : response.currency;

                if (this.isClick) {
                    this.isRendered = true;
                    this.afterClickAction();
                }
                builder.setClientToken(config.clientToken)
                    .setOptions(config.options)
                    .setName('paypal')
                    .setContainer(config.containerId)
                    .setPayment(config.paymentId)
                    .setDetails(config.detailsId)
                    .setFormAction(config.formAction)
                    .build();
            },

            /**
             * @param {*} event
             * @returns
             */
            mousedown: function (event) {
                this.isClick = true;
                event.preventDefault();
                event.stopPropagation();

                $(config.containerId).parents('form:first')
                    .find('.action.primary:first')
                    .click();
            },

            /**
             * @param {*} event
             * @returns
             */
            click: function (event) {

                if (this.isAfterClick) {
                    this.isAfterClick = false;
                    this.isClick = false;

                    return;
                }

                event.preventDefault();
                event.stopPropagation();
            }
        };

        ensureUniqueIds(config, element);

        $(element)
            .on('mousedown', button.mousedown.bind(button))
            .on('click', button.click.bind(button));

        customerData.get('cart')
            .subscribe(function () {
                if (this.isClick) {
                    actionGetData.request(config.url)
                        .done(this.update.bind(this));
                }
            }.bind(button));

        actionGetData.when(config.url)
            .promise()
            .done(button.update.bind(button));
    };
});
