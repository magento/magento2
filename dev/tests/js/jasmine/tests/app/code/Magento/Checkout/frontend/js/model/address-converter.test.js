/*
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*eslint-disable max-nested-callbacks*/
/*jscs:disable jsDoc*/
define(['squire'], function (Squire) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Customer/js/customer-data': {
                get: function () {
                    var countryData = {
                        GB: {
                            name: 'United Kingdom'
                        },
                        US: {
                            name: 'United States',
                            regions: {
                                12: {
                                    code: 'CA',
                                    name: 'California'
                                },
                                43: {
                                    code: 'NY',
                                    name: 'New York'
                                },
                                57: {
                                    code: 'TX',
                                    name: 'Texas'
                                }
                            }
                        }
                    };

                    return jasmine.createSpy().and.returnValue(countryData);
                }
            }
        },
        address,
        model;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/model/address-converter'], function (addressConverter) {
            window.checkoutConfig = {
                defaultCountryId: 'US'
            };
            model = addressConverter;
            done();
        });
    });

    describe('Magento_Checkout/js/model/address-converter', function () {
        describe('formAddressDataToQuoteAddress()', function () {
            describe('snake case field names ', function () {
                it('Check that region name and region code are populated given region id', function () {
                    address = model.formAddressDataToQuoteAddress({
                        'country_id': 'US',
                        'region_id': '57'
                    });
                    expect(address.regionId).toBe('57');
                    expect(address.regionCode).toBe('TX');
                    expect(address.region).toBe('Texas');
                });
                it('Check that region name and region id are populated given region code', function () {
                    address = model.formAddressDataToQuoteAddress({
                        'country_id': 'US',
                        'region_code': 'TX'
                    });
                    expect(address.regionId).toBe('57');
                    expect(address.regionCode).toBe('TX');
                    expect(address.region).toBe('Texas');
                });
                it('Check that region code and region id are NOT populated given region name', function () {
                    address = model.formAddressDataToQuoteAddress({
                        'country_id': 'US',
                        'region': 'Texas'
                    });
                    expect(address.regionId).toBeUndefined();
                    expect(address.regionCode).toBe('');
                    expect(address.region).toBe('');
                });
            });
            describe('camel case field names ', function () {
                it('Check that region name and region code are populated given region id', function () {
                    address = model.formAddressDataToQuoteAddress({
                        'countryId': 'US',
                        'regionId': '57'
                    });
                    expect(address.regionId).toBe('57');
                    expect(address.regionCode).toBe('TX');
                    expect(address.region).toBe('Texas');
                });
                it('Check that region name and region id are populated given region code', function () {
                    address = model.formAddressDataToQuoteAddress({
                        'countryId': 'US',
                        'regionCode': 'TX'
                    });
                    expect(address.regionId).toBe('57');
                    expect(address.regionCode).toBe('TX');
                    expect(address.region).toBe('Texas');
                });
                it('Check that region code and region id are NOT populated given region name', function () {
                    address = model.formAddressDataToQuoteAddress({
                        'countryId': 'US',
                        'region': 'Texas'
                    });
                    expect(address.regionId).toBeUndefined();
                    expect(address.regionCode).toBe('');
                    expect(address.region).toBe('');
                });
            });
        });
    });
});
