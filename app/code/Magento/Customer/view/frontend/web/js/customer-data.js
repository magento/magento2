/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Customer/js/section-config',
    'mage/url',
    'mage/storage',
    'jquery/jquery-storageapi'
], function ($, _, ko, sectionConfig, url) {
    'use strict';

    var options = {},
        storage,
        storageInvalidation,
        invalidateCacheBySessionTimeOut,
        invalidateCacheByCloseCookieSession,
        dataProvider,
        buffer,
        customerData,
        deferred = $.Deferred();

    url.setBaseUrl(window.BASE_URL);
    options.sectionLoadUrl = url.build('customer/section/load');

    //TODO: remove global change, in this case made for initNamespaceStorage
    $.cookieStorage.setConf({
        path: '/',
        expires: 1
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
         * @param {Boolean} forceNewSectionTimestamp
         * @return {*}
         */
        getFromServer: function (sectionNames, forceNewSectionTimestamp) {
            var parameters;

            sectionNames = sectionConfig.filterClientSideSections(sectionNames);
            parameters = _.isArray(sectionNames) ? {
                sections: sectionNames.join(',')
            } : [];
            parameters['force_new_section_timestamp'] = forceNewSectionTimestamp;

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
                    if (name != sectionName) { //eslint-disable-line eqeqeq
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
        init: function () {
            var expiredSectionNames = this.getExpiredSectionNames();

            if (expiredSectionNames.length > 0) {
                _.each(dataProvider.getFromStorage(storage.keys()), function (sectionData, sectionName) {
                    buffer.notify(sectionName, sectionData);
                });
                this.reload(expiredSectionNames, false);
            } else {
                _.each(dataProvider.getFromStorage(storage.keys()), function (sectionData, sectionName) {
                    buffer.notify(sectionName, sectionData);
                });

                if (!_.isEmpty(storageInvalidation.keys())) {
                    this.reload(storageInvalidation.keys(), false);
                }
            }

            if (!_.isEmpty($.cookieStorage.get('section_data_clean'))) {
                this.reload(sectionConfig.getSectionNames(), true);
                $.cookieStorage.set('section_data_clean', '');
            }
        },

        /**
         * Retrieve the list of sections that has expired since last page reload.
         *
         * Sections can expire due to lifetime constraints or due to inconsistent storage information
         * (validated by cookie data).
         *
         * @return {Array}
         */
        getExpiredSectionNames: function () {
            var expiredSectionNames = [],
                cookieSectionTimestamps = $.cookieStorage.get('section_data_ids') || {},
                sectionLifetime = options.expirableSectionLifetime * 60,
                currentTimestamp = Math.floor(Date.now() / 1000),
                sectionData;

            // process sections that can expire due to lifetime constraints
            _.each(options.expirableSectionNames, function (sectionName) {
                sectionData = storage.get(sectionName);

                if (typeof sectionData === 'object' && sectionData['data_id'] + sectionLifetime <= currentTimestamp) {
                    expiredSectionNames.push(sectionName);
                }
            });

            // process sections that can expire due to storage information inconsistency
            _.each(cookieSectionTimestamps, function (cookieSectionTimestamp, sectionName) {
                sectionData = storage.get(sectionName);

                if (typeof sectionData === 'undefined' ||
                    typeof sectionData === 'object' &&
                    cookieSectionTimestamp != sectionData['data_id'] //eslint-disable-line
                ) {
                    expiredSectionNames.push(sectionName);
                }
            });

            return _.uniq(expiredSectionNames);
        },

        /**
         * Check if some sections have to be reloaded.
         *
         * @deprecated Use getExpiredSectionNames instead.
         *
         * @return {Boolean}
         */
        needReload: function () {
            var expiredSectionNames = this.getExpiredSectionNames();

            return expiredSectionNames.length > 0;
        },

        /**
         * Retrieve the list of expired keys.
         *
         * @deprecated Use getExpiredSectionNames instead.
         *
         * @return {Array}
         */
        getExpiredKeys: function () {
            return this.getExpiredSectionNames();
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
         * Avoid using this function directly 'cause of possible performance drawbacks.
         * Each customer section reload brings new non-cached ajax request.
         *
         * @param {Array} sectionNames
         * @param {Boolean} forceNewSectionTimestamp
         * @return {*}
         */
        reload: function (sectionNames, forceNewSectionTimestamp) {
            return dataProvider.getFromServer(sectionNames, forceNewSectionTimestamp).done(function (sections) {
                $(document).trigger('customer-data-reload', [sectionNames]);
                buffer.update(sections);
            });
        },

        /**
         * @param {Array} sectionNames
         */
        invalidate: function (sectionNames) {
            var sectionDataIds,
                sectionsNamesForInvalidation;

            sectionsNamesForInvalidation = _.contains(sectionNames, '*') ? sectionConfig.getSectionNames() :
                sectionNames;

            $(document).trigger('customer-data-invalidate', [sectionsNamesForInvalidation]);
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
         * Checks if customer data is initialized.
         *
         * @returns {jQuery.Deferred}
         */
        getInitCustomerData: function () {
            return deferred.promise();
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
            deferred.resolve();
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

                if (_.isObject(xhr.responseJSON) && !_.isEmpty(_.pick(xhr.responseJSON, redirects))) { //eslint-disable-line
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
