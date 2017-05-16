/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'domReady!'
    ],
    function (
        Component,
        quote,
        $
    ) {
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

                $('.' + this.paypalButtonClass).off('click.' + this.id)
                    .on('click.' + this.id, this.click.bind(this));

                quote.totals.subscribe(function (newValue) {
                    // TODO: check for value
                    this.showLink();
                }, this);

                return this;
            },

            /**
             * Display PayPal in-context checkout link
             */
            showLink: function() {
                $('.' + this.paypalButtonClass).show();
            },

            /**
             * @param {Object} event
             * @returns void
             */
            click: function (event) {
                event.preventDefault();

                $('#' + this.paypalButton).click();
            }
        });
    }
);
