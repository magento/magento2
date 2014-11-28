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
    './client',
    './storages',
    'Magento_Ui/js/lib/registry/registry',
    'Magento_Ui/js/lib/class',
    'Magento_Ui/js/lib/events',
], function(_, Client, storages, registry, Class, EventsBus){
    'use strict';
    
    var defaults = {
        stores: ['data', 'params']
    };

    return Class.extend({
        /**
         * Initializes DataProvider instance.
         * @param {Object} settings - Settings to initialize object with.
         */
        initialize: function(settings) {
            _.extend(this, defaults, settings, settings.config || {});

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
                config  = this[store] || {};

                if(Array.isArray(config)){
                    config = {};
                }

                this[store] = new storage(config);
            }, this);

            return this;
        },

        initClient: function(){
            this.client = new Client({
                urls: {
                    beforeSave: this.validate_url,
                    save:       this.submit_url
                } 
            });

            return this;
        },

        /**
         * Assembles data and submits it using 'utils.submit' method
         */
        save: function(options){
            var data = this.data.get();
            
            this.client.save(data, options);

            return this;
        }
    }, EventsBus);
});