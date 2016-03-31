/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Customer/js/section-config',
    'jquery/jquery-storageapi'
], function ($, _, ko, sectionConfig) {
    'use strict';

    //TODO: remove global change, in this case made for initNamespaceStorage
    $.cookieStorage.setConf({path:'/'});

    var options;
    var storage = $.initNamespaceStorage('mage-cache-storage').localStorage;
    var storageInvalidation = $.initNamespaceStorage('mage-cache-storage-section-invalidation').localStorage;

    var invalidateCacheBySessionTimeOut = function(options) {
        if (new Date($.localStorage.get('mage-cache-timeout')) < new Date()) {
            storage.removeAll();
            var date = new Date(Date.now() + parseInt(options.cookieLifeTime, 10) * 1000);
            $.localStorage.set('mage-cache-timeout', date);
        }
    };

    var invalidateCacheByCloseCookieSession = function() {
        if (!$.cookieStorage.isSet('mage-cache-sessid')) {
            $.cookieStorage.set('mage-cache-sessid', true);
            storage.removeAll();
        }
    };

    var dataProvider = {
        getFromStorage: function (sectionNames) {
            var result = {};
            _.each(sectionNames, function (sectionName) {
                result[sectionName] = storage.get(sectionName);
            });
            return result;
        },
        getFromServer: function (sectionNames, updateSectionId) {
            sectionNames = sectionConfig.filterClientSideSections(sectionNames);
            var parameters = _.isArray(sectionNames) ? {sections: sectionNames.join(',')} : [];
            parameters['update_section_id'] = updateSectionId;
            return $.getJSON(options.sectionLoadUrl, parameters).fail(function(jqXHR) {
                throw new Error(jqXHR);
            });
        }
    };


    ko.extenders.disposableCustomerData = function(target, sectionName) {
        var sectionDataIds, newSectionDataIds = {};
        target.subscribe(function(newValue) {
            setTimeout(function(){
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

    var buffer = {
        data: {},
        bind: function (sectionName) {
            this.data[sectionName] = ko.observable({});
        },
        get: function (sectionName) {
            if (!this.data[sectionName]) {
                this.bind(sectionName);
            }
            return this.data[sectionName];
        },
        keys: function () {
            return _.keys(this.data);
        },
        notify: function (sectionName, sectionData) {
            if (!this.data[sectionName]) {
                this.bind(sectionName);
            }
            this.data[sectionName](sectionData);
        },
        update: function (sections) {
            var sectionId = 0;
            var sectionDataIds = $.cookieStorage.get('section_data_ids') || {};
            _.each(sections, function (sectionData, sectionName) {
                sectionId = sectionData['data_id'];
                sectionDataIds[sectionName] = sectionId;
                storage.set(sectionName, sectionData);
                storageInvalidation.remove(sectionName);
                buffer.notify(sectionName, sectionData);
            });
            $.cookieStorage.set('section_data_ids', sectionDataIds);
        },
        remove: function (sections) {
            _.each(sections, function (sectionName) {
                storage.remove(sectionName);
                if (!sectionConfig.isClientSideSection(sectionName)) {
                    storageInvalidation.set(sectionName, true);
                }
            });
        }
    };

    var customerData = {
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
        needReload: function () {
            var cookieSections = $.cookieStorage.get('section_data_ids');
            if (typeof cookieSections != 'object') {
                return true;
            }
            var storageVal, name;
            for (name in cookieSections) {
                if (undefined !== name) {
                    storageVal = storage.get(name);
                    if (typeof storageVal == 'object' && cookieSections[name] > storageVal['data_id']) {
                        return true;
                    }
                }
            }
            return false;
        },
        getExpiredKeys: function() {
            var cookieSections = $.cookieStorage.get('section_data_ids');

            if (typeof cookieSections != 'object') {
                return [];
            }
            var storageVal, name, expiredKeys = [];
            for (name in cookieSections) {
                storageVal = storage.get(name);
                if (typeof storageVal == 'object' && cookieSections[name] !=  storage.get(name)['data_id']) {
                    expiredKeys.push(name);
                }
            }
            return expiredKeys;
        },
        get: function (sectionName) {
            return buffer.get(sectionName);
        },
        set: function (sectionName, sectionData) {
            var data = {};
            data[sectionName] = sectionData;
            buffer.update(data);
        },
        reload: function (sectionNames, updateSectionId) {
            return dataProvider.getFromServer(sectionNames, updateSectionId).done(function (sections) {
                buffer.update(sections);
            });
        },
        invalidate: function (sectionNames) {
            var sectionDataIds,
                sectionsNamesForInvalidation;

            sectionsNamesForInvalidation = _.contains(sectionNames, '*') ? buffer.keys() : sectionNames;
            buffer.remove(sectionsNamesForInvalidation);
            sectionDataIds = $.cookieStorage.get('section_data_ids') || {};

            // Invalidate section in cookie (increase version of section with 1000)
            _.each(sectionNames, function (sectionName) {
                if (!sectionConfig.isClientSideSection(sectionName)) {
                    sectionDataIds[sectionName] += 1000;
                }
            });
            $.cookieStorage.set('section_data_ids', sectionDataIds);
        },
        'Magento_Customer/js/customer-data': function (settings) {
            options = settings;
            invalidateCacheBySessionTimeOut(settings);
            invalidateCacheByCloseCookieSession();
            customerData.init();
        }
    };

    /** Events listener **/
    $(document).on('ajaxComplete', function (event, xhr, settings) {
        var sections,
            redirects;

        if (settings.type.match(/post|put/i)) {
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
    $(document).on('submit', function (event) {
        var sections;

        if (event.target.method.match(/post|put/i)) {
            sections = sectionConfig.getAffectedSections(event.target.action);
            if (sections) {
                customerData.invalidate(sections);
            }
        }
    });

    return customerData;
});
