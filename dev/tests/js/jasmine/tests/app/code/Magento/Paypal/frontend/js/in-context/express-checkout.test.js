/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire',
    'jquery'
], function (Squire, $) {
    'use strict';

    describe('Magento_Paypal/js/in-context/express-checkout', function () {

        var model,
            event,
            paypalExpressCheckout,
            injector = new Squire(),
            mocks = {
                'paypalInContextExpressCheckout': {
                    checkout: jasmine.createSpyObj('checkout',
                        ['setup', 'initXO', 'startFlow', 'closeFlow']
                    )
                },
                'Magento_Customer/js/customer-data': {
                    set: jasmine.createSpy(),
                    invalidate: jasmine.createSpy()
                }
            };

        /**
         * Run before each test method
         *
         * @return void
         */
        beforeEach(function (done) {
            event = {
                /** Stub */
                preventDefault: jasmine.createSpy('preventDefault')
            };

            injector.mock(mocks);

            injector.require([
                'paypalInContextExpressCheckout',
                'Magento_Paypal/js/in-context/express-checkout'], function (PayPal, Constr) {
                    paypalExpressCheckout = PayPal;
                    model = new Constr();

                    done();
                });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {}
        });

        describe('clientConfig.click method', function () {

            it('Check for properties defined ', function () {
                expect(model.hasOwnProperty('clientConfig')).toBeDefined();
                expect(model.clientConfig.hasOwnProperty('click')).toBeDefined();
                expect(model.clientConfig.hasOwnProperty('checkoutInited')).toBeDefined();
            });

            it('Check properties type', function () {
                expect(typeof model.clientConfig.checkoutInited).toEqual('boolean');
                expect(typeof model.clientConfig.click).toEqual('function');
            });

            it('Check properties value', function () {
                expect(model.clientConfig.checkoutInited).toEqual(false);
            });

            it('Check call "click" method', function () {

                spyOn(jQuery.fn, 'trigger');
                spyOn(jQuery, 'get').and.callFake(function () {
                    var d = $.Deferred();

                    d.resolve({
                        'url': true
                    });

                    return d.promise();
                });

                model.clientConfig.click(event);

                expect(event.preventDefault).toHaveBeenCalled();
                expect(paypalExpressCheckout.checkout.initXO).toHaveBeenCalled();
                expect(model.clientConfig.checkoutInited).toEqual(true);
                expect(jQuery.get).toHaveBeenCalled();
                expect(jQuery('body').trigger).toHaveBeenCalledWith(
                    jasmine.arrayContaining(['processStart'], ['processStop'])
                );
            });

            it('Check call "click" method', function () {
                var message = {
                    text: 'text',
                    type: 'error'
                };

                spyOn(jQuery.fn, 'trigger');
                spyOn(jQuery, 'get').and.callFake(function () {
                    var d = $.Deferred();

                    d.resolve({
                        message: message
                    });

                    return d.promise();
                });

                model.clientConfig.click(event);
                expect(mocks['Magento_Customer/js/customer-data'].set).toHaveBeenCalledWith('messages', {
                    messages: [message]
                });
                expect(event.preventDefault).toHaveBeenCalled();
                expect(paypalExpressCheckout.checkout.initXO).toHaveBeenCalled();
                expect(model.clientConfig.checkoutInited).toEqual(true);
                expect(jQuery.get).toHaveBeenCalled();
                expect(jQuery('body').trigger).toHaveBeenCalledWith(
                    jasmine.arrayContaining(['processStart'], ['processStop'])
                );
            });
        });
    });
});
