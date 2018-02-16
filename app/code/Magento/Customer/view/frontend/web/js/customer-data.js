/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Customer/js/section-config',
    'mage/storage',
    'jquery/jquery-storageapi'
], function ($, _, ko, sectionConfig, mageStorage) {
    'use strict';

    var options,
        storage,
        storageInvalidation,
        invalidateCacheBySessionTimeOut,
        invalidateCacheByCloseCookieSession,
        dataProvider,
        buffer,
        customerData;

    //TODO: remove global change, in this case made for initNamespaceStorage
    $.cookieStorage.setConf({
        path: '/'
    });

    storage = $.initNamespaceStorage('mage-cache-storage').localStorage;
    storageInvalidation = $.initNamespaceStorage('mage-cache-storage-section-invalidation').localStorage;

    /**
     * @param {Object} invalidateOptions
     */
    invalidateCacheBySessionTimeOut = function (invalidateOptions) {
        var date;

        if (new Date($.localStorage.get('mage-cache-timeout')) < new Date()) {
            storage.removeAll();
            date = new Date(Date.now() + parseInt(invalidateOptions.cookieLifeTime, 10) * 1000);
            $.localStorage.set('mage-cache-timeout', date);
        }
    };

    /**
     * Invalidate Cache By Close Cookie Session
     */
    invalidateCacheByCloseCookieSession = function () {
        if (!$.cookieStorage.isSet('mage-cache-sessid')) {
            $.cookieStorage.set('mage-cache-sessid', true);
            storage.removeAll();
        }
    };

    dataProvider = {

        /**
         * @param {Object} sectionNames
         * @return {Object}
         */
        getFromStorage: function (sectionNames) {
            var result = {};

            _.each(sectionNames, function (sectionName) {
                result[sectionName] = storage.get(sectionName);
            });

            return result;
        },

        /**
         * @param {Object} sectionNames
         * @param {Number} updateSectionId
         * @return {*}
         */
        getFromServer: function (sectionNames, updateSectionId) {
            var parameters;

            sectionNames = sectionConfig.filterClientSideSections(sectionNames);
            parameters = _.isArray(sectionNames) ? {
                sections: sectionNames.join(',')
            } : [];
            parameters['update_section_id'] = updateSectionId;

            return $.getJSON(options.sectionLoadUrl, parameters).fail(function (jqXHR) {
                throw new Error(jqXHR);
            });
        }
    };

    /**
     * @param {Function} target
     * @param {String} sectionName
     * @return {*}
     */
    ko.extenders.disposableCustomerData = function (target, sectionName) {
        var sectionDataIds, newSectionDataIds = {};
        target.subscribe(function () {
            setTimeout(function () {
                storage.remove(sectionName);
                sectionDataIds = $.cookieStorage.get('section_data_ids') || {};
                _.each(sectionDataIds, function (data, name) {
                    if (name != sectionName) {
                        newSectionDataIds[name] = data;
                    }
                });
                $.cookieStorage.set('section_data_ids', newSectionDataIds);
            }, 3000);
        });

        return target;
    };

    buffer = {
        data: {},

        /**
         * @param {String} sectionName
         */
        bind: function (sectionName) {
            this.data[sectionName] = ko.observable({});
        },

        /**
         * @param {String} sectionName
         * @return {Object}
         */
        get: function (sectionName) {
            if (!this.data[sectionName]) {
                this.bind(sectionName);
            }

            return this.data[sectionName];
        },

        /**
         * @return {Array}
         */
        keys: function () {
            return _.keys(this.data);
        },

        /**
         * @param {String} sectionName
         * @param {Object} sectionData
         */
        notify: function (sectionName, sectionData) {
            if (!this.data[sectionName]) {
                this.bind(sectionName);
            }
            this.data[sectionName](sectionData);
        },

        /**
         * @param {Object} sections
         */
        update: function (sections) {
            var sectionId = 0,
                sectionDataIds = $.cookieStorage.get('section_data_ids') || {};

            _.each(sections, function (sectionData, sectionName) {
                sectionId = sectionData['data_id'];
                sectionDataIds[sectionName] = sectionId;
                storage.set(sectionName, sectionData);
                storageInvalidation.remove(sectionName);
                buffer.notify(sectionName, sectionData);
            });
            $.cookieStorage.set('section_data_ids', sectionDataIds);
        },

        /**
         * @param {Object} sections
         */
        remove: function (sections) {
            _.each(sections, function (sectionName) {
                storage.remove(sectionName);
                if (!sectionConfig.isClientSideSection(sectionName)) {
                    storageInvalidation.set(sectionName, true);
                }
            });
        }
    };

    customerData = {

        /**
         * Customer data initialization
         */
        init: function() {
            var countryData,
                privateContent = $.cookieStorage.get('private_content_version');

            if (_.isEmpty(storage.keys())) {
                if (!_.isEmpty(privateContent)) {
                    this.reload([], false);
                }
            } else if (this.needReload()) {
                _.each(dataProvider.getFromStorage(storage.keys()), function (sectionData, sectionName) {
                    buffer.notify(sectionName, sectionData);
                });
                this.reload(this.getExpiredKeys(), false);
            } else {
                _.each(dataProvider.getFromStorage(storage.keys()), function (sectionData, sectionName) {
                    buffer.notify(sectionName, sectionData);
                });

                if (!_.isEmpty(storageInvalidation.keys())) {
                    this.reload(storageInvalidation.keys(), false);
                }
            }

            if (!_.isEmpty(privateContent)) {
                countryData = this.get('directory-data');
                if (_.isEmpty(countryData())) {
                    customerData.reload(['directory-data'], false);
                }
            }
        },

        /**
         * @return {Boolean}
         */
        needReload: function () {
            var cookieSections = $.cookieStorage.get('section_data_ids'),
                storageVal,
                name;

            if (typeof cookieSections != 'object') {
                return true;
            }

            for (name in cookieSections) {
                if (name !== undefined) {
                    storageVal = storage.get(name);

                    if (typeof storageVal === 'undefined' ||
                        typeof storageVal == 'object' && cookieSections[name] > storageVal['data_id']
                    ) {
                        return true;
                    }
                }
            }

            return false;
        },

        /**
         *
         * @return {Array}
         */
        getExpiredKeys: function () {
            var cookieSections = $.cookieStorage.get('section_data_ids'),
                storageVal,
                name,
                expiredKeys = [];

            if (typeof cookieSections != 'object') {
                return [];
            }

            for (name in cookieSections) {
                storageVal = storage.get(name);

                if (typeof storageVal === 'undefined' ||
                    typeof storageVal == 'object' && cookieSections[name] !=  storage.get(name)['data_id']
                ) {
                    expiredKeys.push(name);
                }
            }

            return expiredKeys;
        },

        /**
         * @param {String} sectionName
         * @return {*}
         */
        get: function (sectionName) {
            return buffer.get(sectionName);
        },

        /**
         * @param {String} sectionName
         * @param {Object} sectionData
         */
        set: function (sectionName, sectionData) {
            var data = {};

            data[sectionName] = sectionData;
            buffer.update(data);
        },

        /**
         * @param {Array} sectionNames
         * @param {Number} updateSectionId
         * @return {*}
         */
        reload: function (sectionNames, updateSectionId) {
            return dataProvider.getFromServer(sectionNames, updateSectionId).done(function (sections) {
                buffer.update(sections);
            });
        },

        /**
         * @param {Array} sectionNames
         */
        invalidate: function (sectionNames) {
            var sectionDataIds,
                sectionsNamesForInvalidation;

            sectionsNamesForInvalidation = _.contains(sectionNames, '*') ? buffer.keys() : sectionNames;
            buffer.remove(sectionsNamesForInvalidation);
            sectionDataIds = $.cookieStorage.get('section_data_ids') || {};

            // Invalidate section in cookie (increase version of section with 1000)
            _.each(sectionsNamesForInvalidation, function (sectionName) {
                if (!sectionConfig.isClientSideSection(sectionName)) {
                    sectionDataIds[sectionName] += 1000;
                }
            });
            $.cookieStorage.set('section_data_ids', sectionDataIds);
        },

        /**
         * @param {Object} settings
         * @constructor
         */
        'Magento_Customer/js/customer-data': function (settings) {
            options = settings;
            invalidateCacheBySessionTimeOut(settings);
            invalidateCacheByCloseCookieSession();
            customerData.init();
        }
    };

    /**
     * Events listener
     */
    $(document).on('ajaxComplete', function (event, xhr, settings) {
        var sections,
            redirects;

        if (settings.type.match(/post|put|delete/i)) {
            sections = sectionConfig.getAffectedSections(settings.url);

            if (sections) {
                customerData.invalidate(sections);
                redirects = ['redirect', 'backUrl'];

                if (_.isObject(xhr.responseJSON) && !_.isEmpty(_.pick(xhr.responseJSON, redirects))) {
                    return;
                }
                customerData.reload(sections, true);
            }
        }
    });

    /**
     * Events listener
     */
    $(document).on('submit', function (event) {
        var sections;

        if (event.target.method.match(/post|put|delete/i)) {
            sections = sectionConfig.getAffectedSections(event.target.action);

            if (sections) {
                customerData.invalidate(sections);
            }
        }
    });

    return customerData;
});
