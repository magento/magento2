/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/* eslint max-nested-callbacks: 0 */
define([
    'squire'
], function (Squire) {
    'use strict';

    var injector = new Squire(),
        stored,
        checkoutData = {
            /** Stub */
            getRejectedBillingAddresses: function () {
                return stored;
            },

            /** Stub */
            setRejectedBillingAddresses: function (data) {
                stored = data;
            }
        },
        rejected;

    /**
     * @param {*} customerAddressId
     * @param {String} telephone
     * @return {Object}
     */
    function address(customerAddressId, telephone) {
        return {
            customerAddressId: customerAddressId,
            telephone: telephone,
            city: 'Miami',

            /** Stub */
            getKey: function () {
                return customerAddressId ? 'customer-address' + customerAddressId : 'new-customer-billing-address';
            }
        };
    }

    beforeEach(function (done) {
        stored = undefined;
        injector.mock({
            'Magento_Checkout/js/checkout-data': checkoutData
        });
        injector.require(['Magento_Checkout/js/model/rejected-billing-addresses'], function (instance) {
            rejected = instance;
            done();
        });
    });

    describe('Magento_Checkout/js/model/rejected-billing-addresses', function () {
        it('remembers a rejected saved address (#41445)', function () {
            expect(rejected.has(address(7, 'N/A'))).toBeFalse();
            rejected.add(address(7, 'N/A'));
            expect(rejected.has(address(7, 'N/A'))).toBeTrue();
            expect(rejected.has(address(8, 'N/A'))).toBeFalse();
        });

        it('forgets the address once it is corrected in the address book', function () {
            rejected.add(address(7, 'N/A'));
            expect(rejected.has(address(7, '3055550199'))).toBeFalse();
        });

        it('ignores addresses entered in checkout', function () {
            rejected.add(address(null, 'N/A'));
            expect(stored).toBeUndefined();
            expect(rejected.has(address(null, 'N/A'))).toBeFalse();
        });
    });
});
