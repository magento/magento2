/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* jscs:disable */
/* eslint-disable */
define([
    'jquery',
    'mage/cookies'
], function ($) {
    'use strict';

    /**
     * @param {Object} config
     */
    return function (config) {
        /**
        *Magento Developer Id - Used for Gtag Configuration
        */
        var DEVELOPER_ID = 'dYjhlMD';
        var allowServices = false,
            allowedCookies,
            allowedWebsites,
            accountId,
            accountType,
            anonymizedIp;

        if (config.isCookieRestrictionModeEnabled) {
            allowedCookies = $.mage.cookies.get(config.cookieName);

            if (allowedCookies !== null) {
                allowedWebsites = JSON.parse(allowedCookies);

                if (allowedWebsites[config.currentWebsite] === 1) {
                    allowServices = true;
                }
            }
        } else {
            allowServices = true;
        }

        if (allowServices) {
            console.log("GoogleAnalytics - START - gtag config");
            /* Global site tag (gtag.js) - Google Analytics */
            accountId = config.pageTrackingData.accountId;
            accountType = config.pageTrackingData.accountType;
            anonymizedIp = config.pageTrackingData.isAnonymizedIpActive;
            if (gtag) {
                console.log("GoogleAnalytics - gtag exists...");
                gtag('config', accountId, { 'anonymize_ip': anonymizedIp });
            } else {
                console.log("GoogleAnalytics - gtag does not already exist...");
                var gtagScript = document.createElement('script');
                var src_url = 'https://www.googletagmanager.com/gtag/js?id=' + accountId;
                gtagScript.type = 'text/javascript';
                gtagScript.async = true;
                gtagScript.src = src_url;
                document.head.appendChild(gtagScript);
                window.dataLayer = window.dataLayer || [];

                // TODO - add validation for gtag isPresent
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('set', DEVELOPER_ID, true);
                gtag('config', accountId, { 'anonymize_ip': anonymizedIp });

            }
            console.log("GoogleAnalytics - END - gtag config");
            // var gtagScript = document.createElement('script');
            // var src_url = 'https://www.googletagmanager.com/gtag/js?id=' + accountId;
            // gtagScript.type = 'text/javascript';
            // gtagScript.async = true;
            // gtagScript.src = src_url;
            // document.head.appendChild(gtagScript);
            // window.dataLayer = window.dataLayer || [];

            // TODO - add validation for gtag isPresent
            // function gtag(){dataLayer.push(arguments);}
            // gtag('js', new Date());
            // gtag('set', DEVELOPER_ID, true);
            // gtag('config', accountId, { 'anonymize_ip': anonymizedIp });
            // gtag('config', 'conversion-id') // this will be conversion-id for google ads if available
            var currency = config.ordersTrackingData.hasOwnProperty('currency'); //test_logging
            
            // Process orders data
            if (currency) {
                // Collect product data for GA
                if (config.ordersTrackingData.products) {
                    console.log("GoogleAnalytics - START - Add to Cart");
                    var products = config.ordersTrackingData.products;
                    // Universal Analytics Account Type
                    if (accountType === '0') {
                        gtag('event', 'add_to_cart', {
                            'items' : products
                        });
                    // Google Analytics Account Type
                    } else { 
                        let updatedProducts = [];
                        let tempProduct = {};
                        for(let i = 0; i < products.length; i++) {
                            tempProduct = Object.fromEntries(Object.entries(products[i]).map((entry) => { 
                                if (entry[0] === 'id' || entry[0] === 'name') entry[0] = 'item_' + entry[0];
                                return entry;
                            }));
                            updatedProducts.push(tempProduct);
                        }
                        gtag('event', 'add_to_cart', {
                            'items' : updatedProducts
                        });
                    }
                    console.log("GoogleAnalytics - END - Add to Cart");
                }
               
                // Collect orders data for GA
                if (config.ordersTrackingData.orders) {
                    console.log("GoogleAnalytics - START - Purchase");
                    var orders = config.ordersTrackingData.orders;
                    // Universal Analytics Account Type
                    if (accountType === '0') {
                        gtag('event', 'purchase', {
                            'items': orders
                        });
                    // Google Analytics Account Type
                    } else {
                        let updatedOrders = [];
                        let tempOrder = {};
                        for(let i = 0; i < orders.length; i++) {
                            tempOrder = Object.fromEntries(Object.entries(orders[i]).map((entry) => { 
                                if (entry[0] === 'id' || entry[0] === 'name') entry[0] = 'item_' + entry[0];
                                return entry;
                            }));
                            updatedOrders.push(tempOrder);
                        }
                        gtag('event', 'purchase', {
                            'items' : updatedOrders
                        });
                    }
                    console.log("GoogleAnalytics - END - Purchase");
                }
            }
        }
    }
});
