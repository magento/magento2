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
            /* Global site tag (gtag.js) - Google Analytics */
            console.log("~~~~~~~~~~~~~~~~~~~~~ START ~~~~~~~~~~~~~~~~~~~~~~~");
            accountId = config.pageTrackingData.accountId;
            accountType = config.pageTrackingData.accountType;
            anonymizedIp = config.pageTrackingData.isAnonymizedIpActive;
            console.log("~~~~~ accountId:", accountId); //test_logging
            console.log("~~~~~ accountType:", accountType); //test_logging
            console.log("~~~~~ isAnonymizedIpActive:", anonymizedIp); //test_logging
            
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
            // gtag('config', 'conversion-id') // this will be conversion-id for google ads if available
            var currency = config.ordersTrackingData.hasOwnProperty('currency'); //test_logging
            console.log("~~~~~ Currency:", currency);//test_logging
            console.log("~~~~~ config.ordersTrackingData.products:", config.ordersTrackingData.products);
            // Process orders data
            if (currency) {
                // Collect product data for GA
                if (config.ordersTrackingData.products) {
                    var products = config.ordersTrackingData.products;
                    console.log("~~~ PRODUCTS:", products);//test_logging
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
                        console.log("~~~ GA4 - updatedProducts:", updatedProducts);//test_logging
                        gtag('event', 'add_to_cart', {
                            'items' : updatedProducts
                        });
                    }
                }
               
                // Collect orders data for GA
                if (config.ordersTrackingData.orders) {
                    var orders = config.ordersTrackingData.orders;
                    console.log("~~~ ORDERS:", orders);
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
                        console.log("~~~ GA4 - updatedOrders:", updatedOrders);
                        gtag('event', 'purchase', {
                            'items' : updatedOrders
                        });
                    }
                }
            }
        }
    }
});
