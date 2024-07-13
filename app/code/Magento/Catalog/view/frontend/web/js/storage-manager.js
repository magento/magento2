/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiElement',
    'mageUtils',
    'Magento_Catalog/js/product/storage/storage-service',
    'Magento_Customer/js/section-config',
    'jquery'
], function (_, Element, utils, storage, sectionConfig, $) {
    'use strict';

    /**
     * Flush events, that are clones of the same customer data sections
     * Events listener
     */
    $(document).on('submit', function (event) {
        var sections;

        if (event.target.hasAttribute('method') && event.target.getAttribute('method').match(/post|put|delete/i)) {
            sections = sectionConfig.getAffectedSections(event.target.action);

            if (sections && window.localStorage) {
                _.each(sections, function (section) {
                    window.localStorage.removeItem(section);
                });
            }
        }
    });

    return Element.extend({
        defaults: {
            defaultNamespace: {
                lifetime: 1000
            },
            storagesConfiguration: {
                'recently_viewed_product': {
                    namespace: 'recently_viewed_product',
                    className: 'IdsStorage',
                    lifetime: '${ $.defaultNamespace.lifetime }',
                    requestConfig: {
                        typeId: '${ $.storagesConfiguration.recently_viewed_product.namespace }'
                    },
                    savePrevious: {
                        namespace: '${ $.storagesConfiguration.recently_viewed_product.namespace }' + '_previous',
                        className: '${ $.storagesConfiguration.recently_viewed_product.className }'
                    },
                    allowToSendRequest: 0
                },
                'recently_compared_product': {
                    namespace: 'recently_compared_product',
                    className: 'IdsStorageCompare',
                    provider: 'compare-products',
                    lifetime: '${ $.defaultNamespace.lifetime }',
                    requestConfig: {
                        typeId: '${ $.storagesConfiguration.recently_compared_product.namespace }'
                    },
                    savePrevious: {
                        namespace: '${ $.storagesConfiguration.recently_compared_product.namespace }' + '_previous',
                        className: '${ $.storagesConfiguration.recently_compared_product.className }'
                    },
                    allowToSendRequest: 0
                },
                'product_data_storage': {
                    namespace: 'product_data_storage',
                    className: 'DataStorage',
                    allowToSendRequest: 0,
                    updateRequestConfig: {
                        url: '',
                        method: 'GET',
                        dataType: 'json'
                    }
                }
            },
            requestConfig: {
                method: 'POST',
                dataType: 'json',
                ajaxSaveType: 'default',
                ignoreProcessEvents: true
            },
            requestSent: 0
        },

        /**
         * Initializes provider component.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super()
                .prepareStoragesConfig()
                .initStorages()
                .initStartData()
                .initUpdateStorageDataListener();

            return this;
        },

        /**
         * Initializes storages.
         *
         * @returns {Object} Chainable.
         */
        initStorages: function () {
            _.each(this.storagesNamespace, function (name) {
                this[name] = storage.createStorage(this.storagesConfiguration[name]);

                if (this.storagesConfiguration[name].savePrevious) {
                    this[name].previous = storage.createStorage(this.storagesConfiguration[name].savePrevious);
                }
            }.bind(this));

            return this;
        },

        /**
         * Initializes start data.
         *
         * @returns {Object} Chainable.
         */
        initStartData: function () {
            _.each(this.storagesNamespace, function (name) {
                this.updateDataHandler(name, this[name].get());
            }.bind(this));

            return this;
        },

        /**
         * Prepare storages congfig.
         *
         * @returns {Object} Chainable.
         */
        prepareStoragesConfig: function () {
            this.storagesNamespace = _.keys(this.storagesConfiguration);

            _.each(this.storagesNamespace, function (name) {
                this.storagesConfiguration[name].requestConfig = _.extend(
                    utils.copy(this.requestConfig),
                    this.storagesConfiguration[name].requestConfig
                );
            }.bind(this));

            return this;
        },

        /**
         * Prepare date in UTC format (in GMT), and calculate unix timestamp based in seconds
         *
         * @returns {Number}
         * @private
         */
        getUtcTime: function () {
            return new Date().getTime() / 1000;
        },

        /**
         * Initializes listeners to storages "data" property.
         */
        initUpdateStorageDataListener: function () {
            _.each(this.storagesNamespace, function (name) {
                if (this[name].data) {
                    this[name].data.subscribe(this.updateDataHandler.bind(this, name));
                }
            }.bind(this));
        },

        /**
         * Handlers for storages "data" property
         */
        updateDataHandler: function (name, data) {
            var previousData = this[name].previous ? this[name].previous.get() : false;

            if (!_.isEmpty(previousData) &&
                !_.isEmpty(data) &&
                !utils.compare(data, previousData).equal) {
                this[name].set(data);
                this[name].previous.set(data);
                this.sendRequest(name, data);
            } else if (
                _.isEmpty(previousData) &&
                !_.isEmpty(data)
            ) {
                this[name].set(data);
                this.sendRequest(name, data);
            }
        },

        /**
         * Gets last updated time
         *
         * @param {String} name - storage name
         */
        getLastUpdate: function (name) {
            return window.localStorage.getItem(this[name].namespace + '_last_update');
        },

        /**
         * Sets last updated time
         *
         * @param {String} name - storage name
         */
        setLastUpdate: function (name) {
            window.localStorage.setItem(this[name].namespace + '_last_update', this.getUtcTime());
        },

        /**
         * Request handler
         *
         * @param {String} name - storage name
         */
        requestHandler: function (name) {
            this.setLastUpdate(name);
            this.requestSent = 1;
        },

        /**
         * Sends request to server to gets data
         *
         * @param {String} name - storage name
         * @param {Object} data - ids
         */
        sendRequest: function (name, data) {
            var params  = utils.copy(this.storagesConfiguration[name].requestConfig),
                url = params.syncUrl,
                typeId = params.typeId;

            if (this.requestSent || !~~this.storagesConfiguration[name].allowToSendRequest) {
                return;
            }

            delete params.typeId;
            delete params.url;
            this.requestSent = 1;

            return utils.ajaxSubmit({
                url: url,
                data: {
                    ids: data,
                    'type_id': typeId
                }
            }, params).done(this.requestHandler.bind(this, name));
        }
    });
});
