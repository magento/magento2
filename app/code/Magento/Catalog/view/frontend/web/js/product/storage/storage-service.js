/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'mageUtils',
    'mage/translate',
    'Magento_Catalog/js/product/storage/ids-storage',
    'Magento_Catalog/js/product/storage/data-storage',
    'Magento_Catalog/js/product/storage/ids-storage-compare'
], function ($, _, utils, $t, IdsStorage, DataStore, IdsStorageCompare) {
    'use strict';

    return (function () {

        var /**
             * {Object} storages - list of storages
             */
            storages = {},

            /**
             * {Object} classes - list of classes
             */
            classes = {},

            /**
             * {Object} prototype - methods that will be added to all storage classes to prototype property.
             */
            prototype = {

                /**
                 * Sets data to storage
                 *
                 * @param {*} data
                 */
                set: function (data) {
                    if (!utils.compare(data, this.data()).equal) {
                        this.data(data);
                    }
                },

                /**
                 * Adds some data to current storage data
                 *
                 * @param {*} data
                 */
                add: function (data) {
                    if (!_.isEmpty(data)) {
                        this.data(_.extend(utils.copy(this.data()), data));
                    }
                },

                /**
                 * Gets current storage data
                 *
                 * @returns {*} data
                 */
                get: function () {
                    return this.data();
                }
            },

            /**
             * Required properties to storage
             */
            storagesInterface =  {
                data: 'function',
                initialize: 'function',
                namespace: 'string'
            },

            /**
             * Private service methods
             */
            _private = {

                /**
                 * Overrides class method and add ability use _super to call parent method
                 *
                 * @param {Object} extensionMethods
                 * @param {Object} originInstance
                 */
                overrideClassMethods: function (extensionMethods, originInstance) {
                    var methodsName = _.keys(extensionMethods),
                        i = 0,
                        length = methodsName.length;

                    for (i; i < length; i++) {
                        if (_.isFunction(originInstance[methodsName[i]])) {
                            originInstance[methodsName[i]] = extensionMethods[methodsName[i]];
                        }
                    }

                    return originInstance;
                },

                /**
                 * Checks is storage implement interface
                 *
                 * @param {Object} classInstance
                 *
                 * @returns {Boolean}
                 */
                isImplementInterface: function (classInstance) {
                    _.each(storagesInterface, function (key, value) {
                        if (typeof classInstance[key] !== value) {
                            return false;
                        }
                    });

                    return true;
                }
            },

            /**
             * Subscribers list
             */
            subscribers = {};

        (function () {
            /**
             * @param {Object} config
             * @return void
             */
            classes[IdsStorage.name] = function (config) {
                _.extend(this, IdsStorage, config);
            };

            /**
             * @param {Object} config
             * @return void
             */
            classes[IdsStorageCompare.name] = function (config) {
                _.extend(this, IdsStorageCompare, config);
            };

            /**
             * @param {Object} config
             * @return void
             */
            classes[DataStore.name] = function (config) {
                _.extend(this, DataStore, config);
            };

            _.each(classes, function (classItem) {
                classItem.prototype = prototype;
            });
        })();

        return {

            /**
             * Creates new storage or returns if storage with declared namespace exist
             *
             * @param {Object} config - storage config
             * @throws {Error}
             * @returns {Object} storage instance
             */
            createStorage: function (config) {
                var instance,
                    initialized;

                if (storages[config.namespace]) {
                    return storages[config.namespace];
                }

                instance = new classes[config.className](config);

                if (_private.isImplementInterface(instance)) {
                    initialized = storages[config.namespace] = instance.initialize();
                    this.processSubscribers(initialized, config);

                    return initialized;
                }

                throw new Error('Class ' + config.className + $t('does not implement Storage Interface'));
            },

            /**
             * Process subscribers
             *
             * Differentiate subscribers by their namespaces: recently_viewed or recently_compared
             * and process callbacks. Callbacks can be add through onStorageInit function
             *
             * @param {Object} initialized
             * @param {Object} config
             * @return void
             */
            processSubscribers: function (initialized, config) {
                if (subscribers[config.namespace]) {
                    _.each(subscribers[config.namespace], function (callback) {
                        callback(initialized);
                    });

                    delete subscribers[config.namespace];
                }
            },

            /**
             * Listens storage creating by namespace
             *
             * @param {String} namespace
             * @param {Function} callback
             * @return void
             */
            onStorageInit: function (namespace, callback) {
                if (storages[namespace]) {
                    callback(storages[namespace]);
                } else {
                    subscribers[namespace] ?
                        subscribers[namespace].push(callback) :
                        subscribers[namespace] = [callback];
                }
            },

            /**
             * Gets storage by namespace
             *
             * @param {String} namespace
             *
             * @returns {Object} storage insance
             */
            getStorage: function (namespace) {
                return storages[namespace];
            }
        };
    })();
});

