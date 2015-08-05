/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'underscore',
        'uiComponent',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/action/set-shipping-information'
    ],
    function (ko, _, Component, shippingService, priceUtils, quote, selectShippingMethod, setShippingInformation) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/cart/shipping-rates'
            },
            shippingRates: shippingService.getShippingRates(),
            shippingRateGroups: ko.observableArray([]),

            /**
             * @override
             */
            initProperties: function () {
                var self = this;
                this._super();

                this.shippingRates.subscribe(function (rates) {
                    _.each(rates, function (rate) {
                        var carrierTitle = rate['carrier_title'];

                        if (self.shippingRateGroups.indexOf(carrierTitle) === -1) {
                            self.shippingRateGroups.push(carrierTitle);
                        }
                    });
                });

                return this;
            },

            /**
             * Get shipping rates for specific group based on title.
             * @returns Array
             */
            getRatesForGroup: function (shippingRateGroupTitle) {
                return _.filter(this.shippingRates(), function (rate) {
                    return shippingRateGroupTitle === rate['carrier_title'];
                });
            },

            /**
             * Format shipping price.
             * @returns {String}
             */
            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },

            /**
             * Set shipping method.
             * @param {String} methodCode
             * @param {String} carrierCode
             * @returns void
             */
            selectShippingMethod: function (methodCode, carrierCode) {
                selectShippingMethod({
                    'carrier_code': carrierCode,
                    'method_code': methodCode
                });
            },

            /**
             * Set shipping method and update totals.
             * @returns void
             */
            setShippingMethod: function () {
                setShippingInformation().done(
                    function () {
                        console.log(ko.unwrap(quote.getTotals()));
                    }
                );
            }
        });
    }
);
