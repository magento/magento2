/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [],
    function() {
        "use strict";
        return {
            serviceUrls: {
                'estimateShippingMethodsForNewAddress': {
                    'guest': '/guest-carts/:quoteId/estimate-shipping-methods',
                    'customer': '/carts/mine/estimate-shipping-methods'
                },
                'estimateShippingMethodsByAddressId': {
                    'default': '/carts/mine/estimate-shipping-methods-by-address-id'
                },
                'estimateShippingMethodsForGiftRegistry': {
                    'guest': '/guest-giftregistry/:quoteId/estimate-shipping-methods',
                    'customer': '/giftregistry/mine/estimate-shipping-methods'
                },
                'getCartTotals': {
                    'guest': '/guest-carts/:quoteId/totals',
                    'customer': '/carts/mine/totals'
                }
            },

            /** Get urls for services */
            getServiceUrls: function() {
                return this.serviceUrls;
            },

            /** Set url for service */
            setServiceUrl: function(serviceCallName, urls) {
                this.serviceUrls[serviceCallName] = urls;
            }
        };
    }
);
