/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/model/new-customer-address'
], function (NewCustomerAddress) {
    'use strict';

    describe('Magento_Checkout/js/model/new-customer-address', function () {
        var newCustomerAddress;

        beforeEach(function () {

            window.checkoutConfig = {
                defaultCountryId: 'US'
            };

            newCustomerAddress = NewCustomerAddress;
        });

        it('Check that is executable.', function () {
            expect(typeof newCustomerAddress).toEqual('function');
        });

        it('Check on empty object.', function () {
            var expected = {
                countryId: 'US',
                regionCode: null,
                region: null
            };

            expect(JSON.stringify(newCustomerAddress({}))).toEqual(JSON.stringify(expected));
        });

        it('Check on function call with empty address data.', function () {
            var result = newCustomerAddress({});

            expect(result.isDefaultShipping()).toBeUndefined();
            expect(result.isDefaultBilling()).toBeUndefined();
            expect(result.getType()).toEqual('new-customer-address');
            expect(result.getKey()).toEqual('new-customer-address');
            expect(result.getKey()).toContain('new-customer-address');
            expect(result.isEditable()).toBeTruthy();
            expect(result.canUseForBilling()).toBeTruthy();
        });

        it('Check on regionId with region object in address data.', function () {
            var result = newCustomerAddress({
                    region: {
                        'region_id': 1
                    }
                }),
                expected = {
                    countryId: 'US',
                    regionId: 1
                };

            expect(JSON.stringify(result)).toEqual(JSON.stringify(expected));
        });
        it('Check on regionId with countryId in address data.', function () {
            var result = newCustomerAddress({
                    'country_id': 'US'
                }),
                expected = {
                    countryId: 'US',
                    regionCode: null,
                    region: null
                };

            expect(JSON.stringify(result)).toEqual(JSON.stringify(expected));
        });
    });
});
