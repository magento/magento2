/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Customer/js/section-config',
    'jquery/jquery-storageapi',
    'jquery/jquery.cookie'
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
        getFromServer: function (sectionNames) {
            var parameters = _.isArray(sectionNames) ? {sections: sectionNames.join(',')} : [];
            return $.getJSON(options.sectionLoadUrl, parameters).fail(function(jqXHR) {
                throw new Error(jqXHR);
            });
        }
    };


    ko.extenders.disposableCustomerData = function(target, sectionName) {
        storage.remove(sectionName);
        target.subscribe(function(newValue) {
            setTimeout(function(){
                storage.remove(sectionName);
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
            _.each(sections, function (sectionData, sectionName) {
                storage.set(sectionName, sectionData);
                storageInvalidation.remove(sectionName);
                buffer.notify(sectionName, sectionData);
            });
        },
        remove: function (sections) {
            _.each(sections, function (sectionName) {
                storage.remove(sectionName);
                storageInvalidation.set(sectionName, true);
            });
        }
    };

    var customerData = {
        init: function() {
            if (_.isEmpty(storage.keys())) {
                this.reload();
            } else {
                _.each(dataProvider.getFromStorage(storage.keys()), function (sectionData, sectionName) {
                    buffer.notify(sectionName, sectionData);
                });
                if (!_.isEmpty(storageInvalidation.keys())) {
                    this.reload(storageInvalidation.keys());
                }
            }
        },
        get: function (sectionName) {
            return buffer.get(sectionName);
        },
        reload: function (sectionNames) {
            return dataProvider.getFromServer(sectionNames).done(function (sections) {
                buffer.update(sections);
            });
        },
        invalidate: function (sectionNames) {
            buffer.remove(_.contains(sectionNames, '*') ? buffer.keys() : sectionNames);
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
        if (settings.type.match(/post|put/i)) {
            var sections = sectionConfig.getAffectedSections(settings.url);
            if (sections) {
                customerData.invalidate(sections);
                var redirects = ['redirect', 'backUrl'];
                if (_.isObject(xhr.responseJSON) && !_.isEmpty(_.pick(xhr.responseJSON, redirects))) {
                    return ;
                }
                customerData.reload(sections);
            }
        }
    });
    $(document).on('submit', function (event) {
        if (event.target.method.match(/post|put/i)) {
            var sections = sectionConfig.getAffectedSections(event.target.action);
            if (sections) {
                customerData.invalidate(sections);
            }
        }
    });

    return customerData;
});
