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
     * @param {HTMLElement} el - Element upon which this module was called.
     * @param {Object} settings
     */
    function init(el, settings) {
        var name    = settings.name,
            config  = getConfig(settings);

        registry.set(name, new DataProvider(config));
    }

    return init;
});