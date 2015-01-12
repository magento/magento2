/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    './data_provider',
    'Magento_Ui/js/lib/registry/registry'
], function($, DataProvider, registry) {
    'use strict';

    /**
     * Merges passed settings with preset ajax properties
     * @param  {Object} settings
     * @returns {Object} - mutated settings
     */
    function getConfig(settings) {
        var config = settings.config,
            client = config.client = config.client || {};

        $.extend(true, client, {
            ajax: {
                data: {
                    name: settings.name,
                    form_key: FORM_KEY
                }
            }
        });

        return settings;
    }

    /**
     * Creates new data provider and register it by settings.name 
     * @param {Object} settings
     */
    function init(settings) {
        var name    = settings.name,
            config  = getConfig(settings);

        registry.set(name, new DataProvider(config));
    }

    return init;
});