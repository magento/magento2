/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */

define(['squire', 'ko'], function (Squire, ko) {
    'use strict';

    var injector = new Squire(),
        checkoutProvider = {
            on: jasmine.createSpy()
        },
        mocks = {
            'Magento_Checkout/js/action/select-shipping-address': jasmine.createSpy(),
            'Magento_Checkout/js/model/address-converter': {
                formAddressDataToQuoteAddress: jasmine.createSpy()
            },
            'Magento_Checkout/js/model/cart/estimate-service': jasmine.createSpy(),
            'Magento_Checkout/js/checkout-data': jasmine.createSpy(),
            'Magento_Checkout/js/model/shipping-rates-validator': {
                bindChangeHandlers: jasmine.createSpy()
            },
            'uiRegistry': {
                async: jasmine.createSpy().and.returnValue(function (callback) {
                    callback(checkoutProvider);
                }),
                create: jasmine.createSpy(),
                get: jasmine.createSpy(),
                set: jasmine.createSpy()
            },
            'Magento_Checkout/js/model/quote': {
                isVirtual: jasmine.createSpy(),
                shippingAddress: jasmine.createSpy()
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                resolveEstimationAddress: jasmine.createSpy()
            },
            'mage/validation': jasmine.createSpy()
        },
        obj;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/view/cart/shipping-estimation'], function (Constr) {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: ''
            });
            done();
        });
    });

    describe('Magento_Checkout/js/view/cart/shipping-estimation', function () {
        describe('"initElement" method', function () {
            it('Check for return value and element that initiated.', function () {
                var element = jasmine.createSpyObj('element', ['initContainer']);

                expect(obj.initElement(element)).toBe(obj);
                expect(mocks['Magento_Checkout/js/model/shipping-rates-validator'].bindChangeHandlers)
                    .not.toHaveBeenCalled();
            });
            it('Check shipping rates validator call.', function () {
                var element = {
                    index: 'address-fieldsets',
                    elems: ko.observable(),
                    initContainer: jasmine.createSpy()
                };

                spyOn(element.elems, 'subscribe');

                obj.initElement(element);
                expect(mocks['Magento_Checkout/js/model/shipping-rates-validator'].bindChangeHandlers)
                    .toHaveBeenCalledWith(element.elems(), true, 500);
                expect(element.elems.subscribe)
                    .toHaveBeenCalledWith(jasmine.any(Function));
            });
        });

        describe('"getEstimationInfo" method', function () {
            it('Check for invalid form data.', function () {
                obj.source = {
                    get: jasmine.createSpy().and.returnValue(true),
                    set: jasmine.createSpy(),
                    trigger: jasmine.createSpy()
                };

                expect(obj.getEstimationInfo()).toBeUndefined();
                expect(obj.source.get).toHaveBeenCalledWith('params.invalid');
                expect(obj.source.get).not.toHaveBeenCalledWith('shippingAddress');
                expect(obj.source.set).toHaveBeenCalledWith('params.invalid', false);
                expect(obj.source.trigger).toHaveBeenCalledWith('shippingAddress.data.validate');
                expect(mocks['Magento_Checkout/js/action/select-shipping-address']).not.toHaveBeenCalled();
                obj.source = {};
            });
            it('Check for vaild form data.', function () {
                obj.source = {
                    get: jasmine.createSpy().and.returnValues(false, {}),
                    set: jasmine.createSpy(),
                    trigger: jasmine.createSpy()
                };

                expect(obj.getEstimationInfo()).toBeUndefined();
                expect(obj.source.get).toHaveBeenCalledWith('params.invalid');
                expect(obj.source.get).toHaveBeenCalledWith('shippingAddress');
                expect(obj.source.set).toHaveBeenCalledWith('params.invalid', false);
                expect(obj.source.trigger).toHaveBeenCalledWith('shippingAddress.data.validate');
                expect(mocks['Magento_Checkout/js/action/select-shipping-address']).toHaveBeenCalled();
                obj.source = {};
            });
        });
    });
});
