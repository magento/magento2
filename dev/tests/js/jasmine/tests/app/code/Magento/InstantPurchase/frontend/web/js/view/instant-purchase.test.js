/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'squire',
    'ko',
    'jquery',
    'mage/validation'
], function (Squire, ko, $) {
    'use strict';

    var injector = new Squire(),
        customerDataMock = ko.observable({}),
        mocks = {
            'Magento_Customer/js/customer-data': {
                /**
                 * Return customer data mock.
                 * @return {*}
                 */
                get: function () {
                    return customerDataMock;
                }
            },
            'Magento_Ui/js/modal/confirm': jasmine.createSpy()
        },
        obj;

    describe('Magento_InstantPurchase/js/view/instant-purchase', function () {
        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require(['Magento_InstantPurchase/js/view/instant-purchase'], function (Constr) {
                obj = Constr({
                    index: 'purchase'
                });
                done();
            });
            $('body').append('<div id="product_addtocart_form"></div>');
        });

        afterEach(function () {
            $('#product_addtocart_form').remove();

            try {
                injector.clean();
                injector.remove();
            } catch (e) {}
        });

        it('Check initialized data.', function () {
            expect(obj.showButton()).toBeFalsy();
            expect(obj.paymentToken()).toBeFalsy();
            expect(obj.shippingAddress()).toBeFalsy();
            expect(obj.billingAddress()).toBeFalsy();
            expect(obj.shippingMethod()).toBeFalsy();
        });

        it('Check when customer data exists.', function () {
            var dataStub = {
                available: true,
                paymentToken: 'paymentToken',
                shippingAddress: 'shippingAddress',
                billingAddress: 'billingAddress',
                shippingMethod: 'shippingMethod'
            };

            customerDataMock(dataStub);

            expect(obj.showButton()).toBe(dataStub.available);
            expect(obj.paymentToken()).toBe(dataStub.paymentToken);
            expect(obj.shippingAddress()).toBe(dataStub.shippingAddress);
            expect(obj.billingAddress()).toBe(dataStub.billingAddress);
            expect(obj.shippingMethod()).toBe(dataStub.shippingMethod);
        });

        it('Check "setPurchaseData".', function () {
            var dataStub = {
                available: true,
                paymentToken: 'paymentToken',
                shippingAddress: 'shippingAddress',
                billingAddress: 'billingAddress',
                shippingMethod: 'shippingMethod'
            };

            obj.setPurchaseData(dataStub);

            expect(obj.showButton()).toBe(dataStub.available);
            expect(obj.paymentToken()).toBe(dataStub.paymentToken);
            expect(obj.shippingAddress()).toBe(dataStub.shippingAddress);
            expect(obj.billingAddress()).toBe(dataStub.billingAddress);
            expect(obj.shippingMethod()).toBe(dataStub.shippingMethod);
        });

        it('Check "instantPurchase" with failed validation.', function () {
            spyOn(jQuery.fn, 'valid').and.returnValue(false);
            expect(obj.instantPurchase()).toBeUndefined();
            expect(mocks['Magento_Ui/js/modal/confirm']).not.toHaveBeenCalled();
        });

        it('Check "instantPurchase" with success validation.', function () {
            spyOn(jQuery.fn, 'valid').and.returnValue(true);
            expect(obj.instantPurchase()).toBeUndefined();
            expect(mocks['Magento_Ui/js/modal/confirm']).toHaveBeenCalled();
        });
    });
});
