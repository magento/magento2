/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    "use strict";
    /**
     * Currently Magento App stores both  region_id and region (as text) values.
     * To prevent missing region (as text) we need to copy it in hidden field.
     * @param Array config
     * @param string element
     *
     * @return void
     */
    return function (config, element) {
        var form = $(element),
            regionId = form.find('#region_id'),
            /**
             * Set region callback
             *
             * @return void
             */
            setRegion = function() {
                form.find('#region').val(regionId.filter(':visible').find(':selected').text());
            };

        if (regionId.is('visible')) {
            setRegion();
        }

        regionId.on('change', setRegion);
        form.find('#country_id').on('change', setRegion);
    };
});
