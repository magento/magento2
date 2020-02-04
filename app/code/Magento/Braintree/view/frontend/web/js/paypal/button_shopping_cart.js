/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Braintree/js/paypal/button',
        'Magento_Checkout/js/model/quote',
        'domReady!'
    ],
    function (
        Component,
        quote
    ) {
        'use strict';

        return Component.extend({

            /**
             * Overrides amount with a value from quote.
             *
             * @returns {Object}
             * @private
             */
            getClientConfig: function (data) {
                var config = this._super(data);

                if (config.amount !== quote.totals()['base_grand_total']) {
                    config.amount = quote.totals()['base_grand_total'];
                }

                return config;
            }
        });
    }
);
