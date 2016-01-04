/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'uiComponent',
        'jquery',
        'domReady!'
    ],
    function (
        Component,
        $
    ) {
        'use strict';

        return Component.extend({

            defaults: {},

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super();

                return this.initEvents();
            },

            /**
             * @returns {Object}
             */
            initEvents: function () {
                $('#' + this.id).off('click.' + this.id)
                    .on('click.' + this.id, this.click.bind(this));

                return this;
            },

            /**
             * @param {Object} event
             * @returns void
             */
            click: function (event) {
                var $paypalButton = $('#' + this.paypalButton),
                    $addToCartButton = $('#' + this.id)
                        .parents('form:first')
                        .find(this.addToCartSelector);

                event.preventDefault();

                $('body').trigger('processStart');

                $paypalButton.off('cartUpdate.' + this.id)
                    .on('cartUpdate.' + this.id, this.cartUpdate.bind(this));

                if (!$addToCartButton.length) {
                    $paypalButton.click();

                    return;
                }

                $addToCartButton.click();
            },

            /**
             * @returns void
             */
            cartUpdate: function () {
                $('#' + this.paypalButton).click();
            }
        });
    }
);
