/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
     * @param {HTMLElement} el -
            Element upon which compononet is going to be initialized.
     * @param {Object} base -
            Basic configuration.
     */
    function init(data, el, base) {
        var providerName    = base.parent_name,
            component       = providerName + ':' + base.name,
            mainComponent   = providerName + ':' + providerName,
            deps            = [providerName];

        if (registry.has(component)) {
            return;
        }

        if (component !== mainComponent) {
            deps.push(mainComponent);
        }

        registry.get(deps, function(provider) {
            var config = getConfig(provider, base);

            registry.set(component, new data.constr(config));
        });
    }

    return function(data) {
        return init.bind(this, data);
    };
});