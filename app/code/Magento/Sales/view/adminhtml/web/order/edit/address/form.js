/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Currently Magento App stores both  region_id and region (as text) values.
     * To prevent missing region (as text) we need to copy it in hidden field.
     * @param {Array} config
     * @param {String} element
     */
    return function (config, element) {
        var form = $(element),
            regionId = form.find('#region_id'),

            /**
             * Set region callback
             */
            setRegion = function () {
                form.find('#region').val(regionId.filter(':visible').find(':selected').text());
            };

        if (regionId.is('visible')) {
            setRegion();
        }

        regionId.on('change', setRegion);
        form.find('#country_id').on('change', setRegion);
    };
});
