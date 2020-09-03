/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint-disable max-nested-callbacks*/
/*jscs:disable jsDoc*/

define([
    'jquery',
    'underscore',
    'Magento_Customer/js/section-config',
    'Magento_Customer/js/customer-data'
], function (
    $,
    _,
    sectionConfig,
    customerData
) {
    'use strict';

    var sectionConfigSettings = {
            baseUrls: [
                'http://localhost/'
            ],
            sections: {
                'customer/account/loginpost': ['*'],
                'checkout/cart/add': ['cart'],
                'rest/*/v1/guest-carts/*/selected-payment-method': ['cart','checkout-data'],
                '*': ['messages']
            },
            clientSideSections: [
                'checkout-data',
                'cart-data'
            ],
            sectionNames: [
                'customer',
                'product_data_storage',
                'cart',
                'messages'
            ]
        },
        cookieLifeTime = 3600,
        jQueryGetJSON;

    function init(config) {
        var defaultConfig = {
            sectionLoadUrl: 'http://localhost/customer/section/load/',
            expirableSectionLifetime: 60, // minutes
            expirableSectionNames: ['cart'],
            cookieLifeTime: cookieLifeTime,
            updateSessionUrl: 'http://localhost/customer/account/updateSession/'
        };

        customerData['Magento_Customer/js/customer-data']($.extend({}, defaultConfig, config || {}));
    }

    function setupLocalStorage(sections) {
        var mageCacheStorage = {},
            sectionDataIds = {};

        _.each(sections, function (sectionData, sectionName) {
            sectionDataIds[sectionName] = sectionData['data_id'];

            if (typeof sectionData.content !== 'undefined') {
                mageCacheStorage[sectionName] = sectionData;
            }
        });

        $.localStorage.set(
            'mage-cache-storage',
            mageCacheStorage
        );
        $.cookieStorage.set(
            'section_data_ids',
            sectionDataIds
        );

        $.localStorage.set(
            'mage-cache-timeout',
            new Date(Date.now() + cookieLifeTime * 1000)
        );
        $.cookieStorage.set(
            'mage-cache-sessid',
            true
        );
    }

    function clearLocalStorage() {
        $.cookieStorage.set('section_data_ids', {});

        if (window.localStorage) {
            window.localStorage.clear();
        }
    }

    describe('Magento_Customer/js/customer-data', function () {
        beforeAll(function () {
            clearLocalStorage();
        });

        beforeEach(function () {
            jQueryGetJSON = $.getJSON;
            sectionConfig['Magento_Customer/js/section-config'](sectionConfigSettings);
        });

        afterEach(function () {
            $.getJSON = jQueryGetJSON;
            clearLocalStorage();
        });

        describe('getExpiredSectionNames()', function () {
            it('check that result contains expired section names', function () {
                setupLocalStorage({
                    'cart': {
                        'data_id': Math.floor(Date.now() / 1000) - 61 * 60, // 61 minutes ago
                        'content': {}
                    }
                });
                init();
                expect(customerData.getExpiredSectionNames()).toEqual(['cart']);
            });

            it('check that result doest not contain unexpired section names', function () {
                setupLocalStorage({
                    'cart': {
                        'data_id': Math.floor(Date.now() / 1000) + 60, // in 1 minute
                        'content': {}
                    }
                });
                init();
                expect(customerData.getExpiredSectionNames()).toEqual([]);
            });

            it('check that result contains invalidated section names', function () {
                setupLocalStorage({
                    'cart': { // without storage content
                        'data_id': Math.floor(Date.now() / 1000) + 60 // in 1 minute
                    }
                });

                init();
                expect(customerData.getExpiredSectionNames()).toEqual(['cart']);
            });

            it('check that result does not contain unsupported section names', function () {
                setupLocalStorage({
                    'catalog': { // without storage content
                        'data_id': Math.floor(Date.now() / 1000) + 60 // in 1 minute
                    }
                });

                init();
                expect(customerData.getExpiredSectionNames()).toEqual([]);
            });
        });

        describe('init()', function () {
            it('check that sections are not requested from server, if there are no expired sections', function () {
                setupLocalStorage({
                    'catalog': { // without storage content
                        'data_id': Math.floor(Date.now() / 1000) + 60 // in 1 minute
                    }
                });

                $.getJSON = jasmine.createSpy('$.getJSON').and.callFake(function () {
                    var deferred = $.Deferred();

                    return deferred.promise();
                });

                init();
                expect($.getJSON).not.toHaveBeenCalled();
            });
            it('check that sections are requested from server, if there are expired sections', function () {
                setupLocalStorage({
                    'customer': {
                        'data_id': Math.floor(Date.now() / 1000) + 60 // invalidated,
                    },
                    'cart': {
                        'data_id': Math.floor(Date.now() / 1000) - 61 * 60, // 61 minutes ago
                        'content': {}
                    },
                    'product_data_storage': {
                        'data_id': Math.floor(Date.now() / 1000) + 60, // in 1 minute
                        'content': {}
                    },
                    'catalog': {
                        'data_id': Math.floor(Date.now() / 1000) + 60 // invalid section,
                    },
                    'checkout': {
                        'data_id': Math.floor(Date.now() / 1000) - 61 * 60, // invalid section,
                        'content': {}
                    }
                });

                $.getJSON = jasmine.createSpy('$.getJSON').and.callFake(function () {
                    var deferred = $.Deferred();

                    return deferred.promise();
                });

                init();
                expect($.getJSON).toHaveBeenCalledWith(
                    'http://localhost/customer/section/load/',
                    jasmine.objectContaining({
                        sections: 'cart,customer'
                    })
                );
            });
        });
    });
});
