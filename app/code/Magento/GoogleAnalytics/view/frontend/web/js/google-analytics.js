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
            allowedWebsites;

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
            var src_url = `https://www.googletagmanager.com/gtag/js?id=${config.pageTrackingData.accountId}`;
            document.head.insertAdjacentHTML("beforeend", <script async src={src_url}></script>);
            
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('set', DEVELOPER_ID, true);

            gtag('config', config.pageTrackingData.accountId, { 'anonymize_ip': 'true'});
            // gtag('config', 'conversion-id') // this will be conversion-id for google ads if available

            // TODO: Finish GTAG for Enhanced Ecommerce 
            // Process orders data
            if (config.ordersTrackingData.hasOwnProperty('currency')) {
                ga('require', 'ec', 'ec.js');

                ga('set', 'currencyCode', config.ordersTrackingData.currency);

                // Collect product data for GA
                if (config.ordersTrackingData.products) {
                    $.each(config.ordersTrackingData.products, function (index, value) {
                        ga('ec:addProduct', value);
                    });
                }

                // Collect orders data for GA
                if (config.ordersTrackingData.orders) {
                    $.each(config.ordersTrackingData.orders, function (index, value) {
                        ga('ec:setAction', 'purchase', value);
                    });
                }

                // ga('send', 'pageview');
            } else {
                // Process Data if not orders
                ga('send', 'pageview' + config.pageTrackingData.optPageUrl);
            }
        }
    }
});
