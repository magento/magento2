/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/resource-urls',
        'mageUtils'
    ],
    function(customer, urlBuilder, resourceUrls, utils) {
        "use strict";
        return {
            /** Get url for service */
            getUrl: function(serviceCallName, urlParams) {
                var checkoutMethod = customer.isLoggedIn() ? 'customer' : 'guest',
                    serviceUrls = resourceUrls.getServiceUrls(),
                    url,
                    params = {};

                if (utils.isEmpty(serviceUrls[serviceCallName])) {
                    return 'Provided service call does not exist.';
                }

                if (!utils.isEmpty(serviceUrls[serviceCallName]['default'])) {
                    url = serviceUrls[serviceCallName]['default'];
                } else {
                    url = serviceUrls[serviceCallName][checkoutMethod];
                }

                if (urlParams && typeof(urlParams[checkoutMethod]) != "undefined") {
                    params = urlParams[checkoutMethod];
                }
                return urlBuilder.createUrl(url, params);
            }
        };
    }
);
