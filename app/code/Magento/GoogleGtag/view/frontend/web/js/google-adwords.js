/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* jscs:disable */
/* eslint-disable */
define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * @param {Object} config
     */
    return function (config) {
        if (!window.gtag) {
            // Inject Global Site Tag
            var gtagScript = document.createElement('script');
            gtagScript.type = 'text/javascript';
            gtagScript.async = true;
            gtagScript.src = config.gtagSiteSrc;
            document.head.appendChild(gtagScript);

            window.dataLayer = window.dataLayer || [];

            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('set', 'developer_id.dYjhlMD', true);
            if (config.conversionLabel) {
                gtag(
                    'event',
                    'conversion',
                    {'send_to': config.conversionId + '/'
                            + config.conversionLabel}
                );
            }
        } else {
            gtag('config', config.conversionId);
        }
    }
});
