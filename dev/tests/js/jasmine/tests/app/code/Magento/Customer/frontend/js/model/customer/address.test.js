/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Customer/js/model/customer/address'
], function (CustomerAddress) {
    'use strict';

    describe('Magento_Customer/js/model/customer/address', function () {
        var customerAddress;

        beforeEach(function () {
            customerAddress = CustomerAddress;
        });

        it('Check that is executable.', function () {
            expect(typeof customerAddress).toEqual('function');
        });

        it('Check on empty object.', function () {
            var addressData = {
                region: {}
            };

            expect(JSON.stringify(customerAddress(addressData))).toEqual(JSON.stringify({}));
        });

        it('Check on function call with empty address data.', function () {
            var result = customerAddress({
                region: {}
            });

            expect(result.isDefaultShipping()).toBeUndefined();
            expect(result.isDefaultBilling()).toBeUndefined();
            expect(result.getAddressInline()).toBeUndefined();
            expect(result.getType()).toEqual('customer-address');
            expect(result.getKey()).toContain('customer-address');
            expect(result.getCacheKey()).toContain('customer-address');
            expect(result.isEditable()).toBeFalsy();
            expect(result.canUseForBilling()).toBeTruthy();
        });

        it('Check on regionId with region object in address data.', function () {
            var result = customerAddress({
                    region: {
                        'region_id': 1
                    }
                }),
                expected = {
                    regionId: '1'
                };

            expect(JSON.stringify(result)).toEqual(JSON.stringify(expected));
        });
    });
});
