/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['jquery', 'underscore', 'ko', 'sectionConfig', 'jquery/jquery-storageapi'], function ($, _, ko, sectionConfig) {
    'use strict';

    var ns = $.initNamespaceStorage('mage-cache-storage');
    var storage = ns.localStorage;

    if (!ns.cookieStorage.isSet('mage-cache-sessid')) {
        ns.cookieStorage.set('mage-cache-sessid', true);
        storage.removeAll();
    }

    var canonize = function (url) {
        var a = document.createElement('a');
        a.href = url;
        return a.pathname.replace(/^\/(?:index.php\/)?|\/$/ig,'');
    };

    $(document).on('ajaxComplete', function (event, xhr, settings) {
        if (settings.type == 'POST') {
            var sections = sectionConfig.get(canonize(settings.url));
            if (sections) {
                customerData.reload(sections);
            }
        }
    });

    $(document).on('submit', function (event) {
        if (event.target.method == 'post') {
            var sections = sectionConfig.get(canonize(event.target.action));
            if (sections) {
                customerData.invalidate(sections);
            }
        }
    });

    var getFromStorage = function (sectionsName) {
        var result = {};
        _.each(sectionsName, function (sectionName) {
            result[sectionName] = storage.get(sectionName);
        });
        return result;
    };

    var getFromServer = function (sectionsName) {
        var parameters = _.isArray(sectionsName) ? {sections: sectionsName.join(',')} : [];
        return $.getJSON('/customer/section/load/', parameters).fail(function(jqXHR) {
            throw new Error(jqXHR.responseJSON.message);
        });
    };

    var buffer = {
        data: {},
        bind: function (sectionName) {
            this.data[sectionName] = ko.observable();
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
                buffer.notify(sectionName, sectionData);
                storage.set(sectionName, sectionData);
            });
        },
        remove: function (sections) {
            _.each(sections, function (sectionName) {
                buffer.notify(sectionName, '');
                storage.remove(sectionName);
            });
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
            }
        },
        get: function (sectionName) {
            return buffer.get(sectionName);
        },
        reload: function (sectionNames) {
            getFromServer(sectionNames == '*' ? buffer.keys() : sectionNames).done(function (sections) {
                buffer.update(sections);
            });
        },
        invalidate: function (sectionNames) {
            buffer.remove(sectionNames == '*' ? buffer.keys() : sectionNames);
        }
    };

    customerData.init();

    return customerData;
});
