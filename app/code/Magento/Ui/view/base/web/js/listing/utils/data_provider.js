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
    'underscore',
    './rest',
    'Magento_Ui/js/lib/storage/index',
    'Magento_Ui/js/lib/class',
    'Magento_Ui/js/lib/events'
], function(_, Rest, storages, Class, EventsBus) {
    'use strict';
    
    var defaults = {
        stores: ['config', 'meta', 'data', 'params', 'dump']
    };

    var DataProvider = Class.extend({
        /**
         * Initializes DataProvider instance.
         * @param {Object} settings - Settings to initialize object with.
         */
        initialize: function(settings) {
            _.extend(this, defaults, settings);

            this.initStorages()
                .initClient();
        },

        /**
         * Creates instances of storage objects.
         * @returns {DataProvider} Chainable.
         */
        initStorages: function() {
            var storage,
                config;

            this.stores.forEach(function(store) {
                storage = storages[store];
                config  = this[store];

                this[store] = new storage(config);
            }, this);

            return this;
        },

        /**
         * Creates instances of a REST client.
         * @returns {DataProvider} Chainable.
         */
        initClient: function() {
            var config = this.config.get('client');

            this.client = new Rest(config);

            this.client.on('read', this.onRead.bind(this));

            return this;
        },

        /**
         * Tries to retrieve data from server using REST client.
         * Allways attaches cached parameters to request.
         * @param {Object} [options] - Additional paramters to be attached. 
         * @returns {DataProvider} Chainable.
         */
        refresh: function(options) {
            var stored = this.params.get(),
                params = _.extend({}, stored, options || {});

            this.trigger('beforeRefresh')
                .client.read(params);

            return this;
        },

        /**
         * Updates list of storages with a specified data.
         * @param {Object} data - Data to update storages with.
         * @returns {DataProvider} Chainable.
         */
        updateStorages: function(data) {
            var value;

            this.stores.forEach(function(store) {
                value = data[store];

                if(value){
                    this[store].set(value);
                }
            }, this);

            return this;
        },

        /**
         * Callback method that fires when REST client
         * will resolve requets to the server.
         * @param {Object} result - Server response.
         */
        onRead: function(result) {
            result = {
                data: result.data
            };

            this.updateStorages(result)
                .trigger('refresh', result);
        }
    }, EventsBus);
    
    return DataProvider;
});