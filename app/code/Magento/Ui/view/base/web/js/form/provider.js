/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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