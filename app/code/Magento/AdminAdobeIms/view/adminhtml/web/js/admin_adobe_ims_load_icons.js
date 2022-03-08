/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'underscore',
    'Magento_AdminAdobeIms/js/loadicons'
], function ($, _, loadicons) {
    'use strict';

    var icons = {},

    loadIcons = {
        /**
         * loadicons initialization
         */
        init: function () {
            loadicons(icons.spectrumCssIcons);
            loadicons(icons.spectrumIcons);
        },

        /**
         * @param {Object} iconUrls
         * @constructor
         */
        'Magento_AdminAdobeIms/js/admin_adobe_ims_load_icons': function (iconUrls) {
            icons = iconUrls;
            loadIcons.init();
        }
    };

    return loadIcons;
});
