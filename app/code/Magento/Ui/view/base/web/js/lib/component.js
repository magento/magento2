/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/lib/registry/registry'
], function(registry) {
    'use strict';

    /**
     * Extends configuration that will be retrieved from the data provider
     * with configuration that is stored in a 'baseConfig' object.
     * @param {Object} provider - DataProvider instance.
     * @param {Object} baseConfig - Basic configuration.
     * @returns {Object} Resulting configurational object.
     */
    function getConfig(provider, baseConfig) {
        var configs     = provider.config.get('components'),
            storeConfig = configs[baseConfig.name] || {};

        return _.extend({
            provider: provider
        }, storeConfig, baseConfig);
    }

    /**
     * Creates new instance of a grids' component.
     * @param {Object} data -
            Data object that was passed while creating component initializer.
     * @param {Object} base -
            Basic configuration.
     */
    function init(data, base) {
        var providerName    = base.parent_name,
            component       = providerName + ':' + base.name;

        if (registry.has(component)) {
            return;
        }

        registry.get(providerName, function(provider) {
            var config = getConfig(provider, base);

            registry.set(component, new data.constr(config));
        });
    }

    return function(data) {
        return init.bind(this, data);
    };
});