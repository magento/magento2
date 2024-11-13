/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
/*jscs:disable jsDoc*/
define([
    'squire', 'jquery', 'ko'
], function (Squire, $, ko) {
    'use strict';

    describe('Magento_Customer/js/customer-global-session-loader', function () {
        var injector,
            customer = ko.observable({}),
            mocks = {
                'Magento_Customer/js/customer-data': {
                    get: jasmine.createSpy('get', function () {
                        return customer;
                    }).and.callThrough(),
                    reload: jasmine.createSpy('reload'),
                    getInitCustomerData: function () {}
                }
            },
            deferred,
            customerSessionLoader;

        beforeAll(function () {
            $('body').append('<div id="customerMenu" class="customer-menu">Customer Menu</div>');
        });

        beforeEach(function (done) {
            injector = new Squire();
            injector.mock(mocks);
            injector.require(['Magento_Customer/js/customer-global-session-loader'], function (instance) {
                customerSessionLoader = instance;
                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
                injector = null;
            } catch (e) {
                console.log(e);
            }

            customer({});
        });

        afterAll(function () {
            $('#customerMenu').remove();
        });

        describe('Check customer data preparation process', function () {
            it('Tests that customer data does not reloads if it has first name defined', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'getInitCustomerData').and.callFake(function () {
                    deferred = $.Deferred();

                    deferred.resolve();

                    return deferred.promise();
                });
                customer({
                    firstname: 'First Name'
                });
                customerSessionLoader();

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('customer');
                expect(mocks['Magento_Customer/js/customer-data'].reload).not.toHaveBeenCalled();
            });

            it('Tests that customer data reloads after init, if customer first name is not there', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'getInitCustomerData').and.callFake(function () {
                    deferred = $.Deferred();

                    return deferred.promise();
                });
                customer({
                    _data: null
                });
                customerSessionLoader();

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('customer');
                expect(mocks['Magento_Customer/js/customer-data'].reload).not.toHaveBeenCalled();

                deferred.resolve();

                expect(mocks['Magento_Customer/js/customer-data'].reload).toHaveBeenCalledWith([], false);
            });

            it('Tests that customer data reloads if customer data is not there', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'getInitCustomerData').and.callFake(function () {
                    deferred = $.Deferred();

                     deferred.resolve();

                    return deferred.promise();
                });
                expect(customerSessionLoader()).toBe(undefined);

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('customer');
                expect(mocks['Magento_Customer/js/customer-data'].getInitCustomerData).toHaveBeenCalled();
                expect(mocks['Magento_Customer/js/customer-data'].reload).toHaveBeenCalledWith([], false);
            });
        });
    });
});
