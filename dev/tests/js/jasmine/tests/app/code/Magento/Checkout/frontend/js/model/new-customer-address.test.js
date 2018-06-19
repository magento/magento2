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
                defaultCountryId: 'US',
                defaultRegionId: 1
            };

            newCustomerAddress = NewCustomerAddress;
        });

        it('Check that is executable.', function () {
            expect(typeof newCustomerAddress).toEqual('function');
        });

        it('Check on empty object.', function () {
            var expected = {
                countryId: 'US',
                regionId: 1,
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

        it('Check on regionId with country object in address data.', function () {
            var result = newCustomerAddress({
                    'country_id': 'CA'
                }),
                expected = {
                    countryId: 'CA',
                    regionCode: null,
                    region: null
                };

            expect(JSON.stringify(result)).toEqual(JSON.stringify(expected));
        });
        it('Check on regionId with countryId and regionId in address data.', function () {
            var result = newCustomerAddress({
                    'country_id': 'CA',
                    region: {
                        'region_id': 66
                    }
                }),
                expected = {
                    countryId: 'CA',
                    regionId: 66
                };

            expect(JSON.stringify(result)).toEqual(JSON.stringify(expected));
        });
    });
});
