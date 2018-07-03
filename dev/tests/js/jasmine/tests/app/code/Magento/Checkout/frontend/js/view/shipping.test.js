/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */
require.config({
    map: {
        '*': {
            'Magento_Checkout/js/view/shipping': 'Magento_Checkout/js/view/shipping'
        }
    }
});

define(['squire', 'ko', 'jquery', 'jquery/validate'], function (Squire, ko, $) {
    'use strict';

    var injector = new Squire(),
        modalStub = {
            openModal: jasmine.createSpy(),
            closeModal: jasmine.createSpy()
        },
        mocks = {
            'Magento_Customer/js/model/customer': {
                isLoggedIn: ko.observable()
            },
            'Magento_Customer/js/model/address-list': ko.observableArray(),
            'Magento_Checkout/js/model/address-converter': jasmine.createSpy(),
            'Magento_Checkout/js/model/quote': {
                isVirtual: jasmine.createSpy(),
                shippingMethod: ko.observable()
            },
            'Magento_Checkout/js/action/create-shipping-address': jasmine.createSpy().and.returnValue(
                jasmine.createSpyObj('newShippingAddress', ['getKey'])
            ),
            'Magento_Checkout/js/action/select-shipping-address': jasmine.createSpy(),
            'Magento_Checkout/js/model/shipping-rates-validator': jasmine.createSpy(),
            'Magento_Checkout/js/model/shipping-address/form-popup-state': {
                isVisible: ko.observable()
            },
            'Magento_Checkout/js/model/shipping-service': jasmine.createSpyObj('service', ['getShippingRates']),
            'Magento_Checkout/js/action/select-shipping-method': jasmine.createSpy(),
            'Magento_Checkout/js/model/shipping-rate-registry': jasmine.createSpy(),
            'Magento_Checkout/js/action/set-shipping-information': jasmine.createSpy(),
            'Magento_Checkout/js/model/step-navigator': jasmine.createSpyObj('navigator', ['registerStep']),
            'Magento_Ui/js/modal/modal': jasmine.createSpy('modal').and.returnValue(modalStub),
            'Magento_Checkout/js/model/checkout-data-resolver': jasmine.createSpyObj(
                'dataResolver',
                ['resolveShippingAddress']
            ),
            'Magento_Checkout/js/checkout-data': jasmine.createSpyObj(
                'checkoutData',
                ['setSelectedShippingAddress', 'setNewCustomerShippingAddress', 'setSelectedShippingRate']
            ),
            'uiRegistry': jasmine.createSpy(),
            'Magento_Checkout/js/model/shipping-rate-service': jasmine.createSpy()
        },
        obj;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/view/shipping'], function (Constr) {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: '',
                popUpForm: {
                    options: {
                        buttons: {
                            save: {},
                            cancel: {}
                        }
                    }
                }
            });
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Magento_Checkout/js/view/shipping', function () {
        describe('"navigate" method', function () {
            it('Check for return value.', function () {
                var step = {
                    isVisible: ko.observable(false)
                };

                expect(obj.navigate(step)).toBeUndefined();
                expect(step.isVisible()).toBe(true);
            });
        });

        describe('"getPopUp" method', function () {
            it('Check for return value.', function () {
                expect(obj.getPopUp()).toBe(modalStub);
                expect(mocks['Magento_Ui/js/modal/modal']).toHaveBeenCalled();
                mocks['Magento_Ui/js/modal/modal'].calls.reset();
            });
            it('Check on single modal call', function () {
                expect(obj.getPopUp()).toBe(modalStub);
                expect(mocks['Magento_Ui/js/modal/modal']).not.toHaveBeenCalled();
            });
        });

        describe('"showFormPopUp" method', function () {
            it('Check method call.', function () {
                expect(obj.showFormPopUp()).toBeUndefined();
                expect(obj.isFormPopUpVisible()).toBeTruthy();
                expect(modalStub.openModal).toHaveBeenCalled();
            });
        });

        describe('"saveNewAddress" method', function () {
            it('Check method call with invalid form data.', function () {
                obj.source = {
                    get: jasmine.createSpy().and.returnValue(true),
                    set: jasmine.createSpy(),
                    trigger: jasmine.createSpy()
                };

                expect(obj.saveNewAddress()).toBeUndefined();
                expect(obj.isNewAddressAdded()).toBeFalsy();
                expect(modalStub.closeModal).not.toHaveBeenCalled();
            });
            it('Check method call with valid form data.', function () {
                obj.source = {
                    get: jasmine.createSpy().and.returnValues(true, false, {}),
                    set: jasmine.createSpy(),
                    trigger: jasmine.createSpy()
                };

                expect(obj.saveNewAddress()).toBeUndefined();
                expect(obj.isNewAddressAdded()).toBeTruthy();
                expect(modalStub.closeModal).toHaveBeenCalled();
            });
        });

        describe('"selectShippingMethod" method', function () {
            it('Check method call.', function () {
                var shippingMethod = {
                    'carrier_code': 'carrier',
                    'method_code': 'method'
                };

                expect(obj.selectShippingMethod(shippingMethod)).toBeTruthy();
                expect(mocks['Magento_Checkout/js/checkout-data'].setSelectedShippingRate)
                    .toHaveBeenCalledWith('carrier_method');
            });
        });

        describe('"setShippingInformation" method', function () {
            it('Check method call.', function () {
                expect(obj.setShippingInformation()).toBeUndefined();
            });
        });

        describe('"validateShippingInformation" method', function () {
            it('Check method call on negative cases.', function () {
                obj.source = {
                    get: jasmine.createSpy().and.returnValue(true),
                    set: jasmine.createSpy(),
                    trigger: jasmine.createSpy()
                };

                expect(obj.validateShippingInformation()).toBeFalsy();
                expect(obj.errorValidationMessage()).toBe(
                    'The shipping method is missing. Select the shipping method and try again.'
                );
                spyOn(mocks['Magento_Checkout/js/model/quote'], 'shippingMethod').and.returnValue(true);
                spyOn(mocks['Magento_Customer/js/model/customer'], 'isLoggedIn').and.returnValue(true);
                expect(obj.validateShippingInformation()).toBeFalsy();
            });
            it('Check method call on positive case.', function () {
                $('body').append('<form data-role="email-with-possible-login">' +
                    '<input type="text" name="username" />' +
                    '</form>');
                obj.source = {
                    get: jasmine.createSpy().and.returnValue(true),
                    set: jasmine.createSpy(),
                    trigger: jasmine.createSpy()
                };
                obj.isFormInline = false;

                spyOn(mocks['Magento_Checkout/js/model/quote'], 'shippingMethod').and.returnValue(true);
                spyOn(mocks['Magento_Customer/js/model/customer'], 'isLoggedIn').and.returnValue(false);
                spyOn($.fn, 'valid').and.returnValue(true);
                expect(obj.validateShippingInformation()).toBeTruthy();
            });
        });

        describe('"triggerShippingDataValidateEvent" method', function () {
            it('Check method call.', function () {
                obj.source = {
                    get: jasmine.createSpy().and.returnValue(true),
                    set: jasmine.createSpy(),
                    trigger: jasmine.createSpy()
                };
                expect(obj.triggerShippingDataValidateEvent()).toBeUndefined();
            });
        });
    });
});
