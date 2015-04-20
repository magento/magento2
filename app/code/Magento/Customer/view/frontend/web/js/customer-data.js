/**
 * Copyright © 2015 Magento. All rights reserved.
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

    var options;
    var ns = $.initNamespaceStorage('mage-cache-storage');
    var storage = ns.localStorage;
    var storageInvalidation = $.initNamespaceStorage('mage-cache-storage-section-invalidation').localStorage;

    storageInvalidation.invalid_sections = 'invalid_sections';
    storageInvalidation.getInvalidSections = function() {
        return this.get(this.invalid_sections) || [];
    };
    storageInvalidation.setInvalidSections = function(sections) {
        return this.set(this.invalid_sections, sections);
    };

    if (!ns.cookieStorage.isSet('mage-cache-sessid')) {
        ns.cookieStorage.set('mage-cache-sessid', true);
        storage.removeAll();
    }

    $(document).on('ajaxSuccess', function (event, xhr, settings) {
        if (settings.type.match(/post/i)) {
            var sections = sectionConfig.getAffectedSections(settings.url);
            if (sections) {
                customerData.reload(sections);
            }
        }
    });

    $(document).on('submit', function (event) {
        if (event.target.method.match(/post/i)) {
            var sections = sectionConfig.getAffectedSections(event.target.action);
            if (sections) {
                customerData.invalidate(sections);
            }
        }
    });

    var getFromStorage = function (sectionNames) {
        var result = {};
        _.each(sectionNames, function (sectionName) {
            result[sectionName] = storage.get(sectionName);
        });
        return result;
    };

    var getFromServer = function (sectionNames) {
        var parameters = _.isArray(sectionNames) ? {sections: sectionNames.join(',')} : [];
        return $.getJSON(options.sectionLoadUrl, parameters).fail(function(jqXHR) {
            throw new Error(jqXHR.responseJSON.message);
        });
    };

    ko.extenders.disposablePrivateData = function(target, sectionName) {
        target.subscribe(function(newValue) {
            storage.remove(sectionName);
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
            storageInvalidation.setInvalidSections([]);
            _.each(sections, function (sectionData, sectionName) {
                storage.set(sectionName, sectionData);
                buffer.notify(sectionName, sectionData);
            });
        },
        remove: function (sections) {
            var invalidSections = storageInvalidation.getInvalidSections();
            _.each(sections, function (sectionName) {
                storage.remove(sectionName);
                invalidSections.push(sectionName);
            });
            storageInvalidation.setInvalidSections(invalidSections);
        }
    };

    var customerData = {
        init: function() {
            if (_.isEmpty(storage.keys())) {
                getFromServer().done(function (sections) {
                    buffer.update(sections);
                });
            } else {
                _.each(getFromStorage(storage.keys()), function (sectionData, sectionName) {
                    buffer.notify(sectionName, sectionData);
                });
                var invalidSections = storageInvalidation.getInvalidSections();
                if (invalidSections.length) {
                    getFromServer(invalidSections).done(function (sections) {
                        buffer.update(sections);
                    });
                }
            }
        },
        get: function (sectionName) {
            return buffer.get(sectionName);
        },
        reload: function (sectionNames) {
            getFromServer(sectionNames).done(function (sections) {
                buffer.update(sections);
            });
        },
        invalidate: function (sectionNames) {
            buffer.remove(_.contains(sectionNames, '*') ? buffer.keys() : sectionNames);
        },
        'Magento_Customer/js/customer-data': function (settings) {
            options = settings;
            customerData.init();
        }
    };

    return customerData;
});
