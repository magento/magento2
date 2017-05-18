/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Paypal/js/in-context/express-checkout'
], function ($, ExpressCheckout) {
    'use strict';

    describe('Magento_Paypal/js/in-context/express-checkout', function () {

        var model, event;

        /**
         * Run before each test method
         *
         * @return void
         */
        beforeEach(function (done) {
            model = new ExpressCheckout();

            event = {
                /** Stub */
                preventDefault: function () {}
            };

            done();
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

                $.ajax = jasmine.createSpy().and.callFake(function () {
                    var d = $.Deferred();

                    d.resolve({
                        'success': true
                    });

                    return d.promise();
                });

                $.fn.trigger = jasmine.createSpy();

                model.clientConfig.click(event);

                expect($.ajax).toHaveBeenCalled();
                expect($('body').trigger).toHaveBeenCalledWith('processStop');
                expect(model.clientConfig.checkoutInited).toEqual(true);
            });
        });
    });
});
