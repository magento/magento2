/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/url-builder',
        'mageUtils'
    ],
    function(customer, urlBuilder, utils) {
        "use strict";
        return {
            getUrlForTotalsEstimationForNewAddress: function(quote) {
                var params = (this.getCheckoutMethod() == 'guest') ? {cartId: quote.getQuoteId()} : {};
                var urls = {
                    'guest': '/guest-carts/:cartId/totals-information',
                    'customer': '/carts/mine/totals-information'
                };
                return this.getUrl(urls, params);
            },

            getUrlForEstimationShippingMethodsForNewAddress: function(quote) {
                var params = (this.getCheckoutMethod() == 'guest') ? {quoteId: quote.getQuoteId()} : {};
                var urls = {
                    'guest': '/guest-carts/:quoteId/estimate-shipping-methods',
                    'customer': '/carts/mine/estimate-shipping-methods'
                };
                return this.getUrl(urls, params);
            },

            getUrlForEstimationShippingMethodsByAddressId: function(quote) {
                var params = (this.getCheckoutMethod() == 'guest') ? {quoteId: quote.getQuoteId()} : {};
                var urls = {
                    'default': '/carts/mine/estimate-shipping-methods-by-address-id'
                };
                return this.getUrl(urls, params);
            },

            getApplyCouponUrl: function(couponCode, quoteId) {
                var params = (this.getCheckoutMethod() == 'guest') ? {quoteId: quoteId} : {};
                var urls = {
                    'guest': '/guest-carts/' + quoteId + '/coupons/' + couponCode,
                    'customer': '/carts/mine/coupons/' + couponCode
                };
                return this.getUrl(urls, params);
            },

            getCancelCouponUrl: function(quoteId) {
                var params = (this.getCheckoutMethod() == 'guest') ? {quoteId: quoteId} : {};
                var urls = {
                    'guest': '/guest-carts/' + quoteId + '/coupons/',
                    'customer': '/carts/mine/coupons/'
                };
                return this.getUrl(urls, params);
            },

            getUrlForCartTotals: function(quote) {
                var params = (this.getCheckoutMethod() == 'guest') ? {quoteId: quote.getQuoteId()} : {};
                var urls = {
                    'guest': '/guest-carts/:quoteId/totals',
                    'customer': '/carts/mine/totals'
                };
                return this.getUrl(urls, params);
            },

            getUrlForSetShippingInformation: function(quote) {
                var params = (this.getCheckoutMethod() == 'guest') ? {cartId: quote.getQuoteId()} : {};
                var urls = {
                    'guest': '/guest-carts/:cartId/shipping-information',
                    'customer': '/carts/mine/shipping-information'
                };
                return this.getUrl(urls, params);
            },

            /** Get url for service */
            getUrl: function(urls, urlParams) {
                var url;

                if (utils.isEmpty(urls)) {
                    return 'Provided service call does not exist.';
                }

                if (!utils.isEmpty(urls['default'])) {
                    url = urls['default'];
                } else {
                    url = urls[this.getCheckoutMethod()];
                }
                return urlBuilder.createUrl(url, urlParams);
            },

            getCheckoutMethod: function() {
                return customer.isLoggedIn() ? 'customer' : 'guest';
            }
        };
    }
);
