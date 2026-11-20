/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'Magento_Paypal/js/in-context/paypal-sdk'
], function ($, loadPaypalScript) {
    'use strict';

    describe('Magento_Paypal/js/in-context/paypal-sdk', function () {
        var originalHandler;

        beforeEach(function () {
            define('paypalSdk', [], function () { return {}; });
            originalHandler = require.s.contexts._.config.onNodeCreated;
            delete require.s.contexts._.config.onNodeCreated;
        });

        afterEach(function () {
            require.s.contexts._.config.onNodeCreated = originalHandler;
            if (require.s.contexts._.config.paths) {
                delete require.s.contexts._.config.paths.paypalSdk;
            }
        });

        describe('onNodeCreated handler', function () {
            it('calls the existing handler before applying PayPal attributes', function () {
                var callOrder = [],
                    existingHandler = jasmine.createSpy('existingHandler').and.callFake(function () {
                        callOrder.push('sri');
                    }),
                    dataAttributes = {'data-namespace': 'paypal'},
                    node = document.createElement('script'),
                    config = {attributes: {paypalSdk: dataAttributes}},
                    capturedHandler;

                require.s.contexts._.config.onNodeCreated = existingHandler;
                spyOn($, 'each').and.callFake(function () {
                    callOrder.push('paypal');
                });

                loadPaypalScript('https://www.paypal.com/sdk/js', dataAttributes);
                capturedHandler = require.s.contexts._.config.onNodeCreated;
                capturedHandler(node, config, 'paypalSdk');

                expect(existingHandler).toHaveBeenCalledWith(node, config, 'paypalSdk');
                expect(callOrder).toEqual(['sri', 'paypal']);
            });

            it('does not error when no prior handler exists', function () {
                var node = document.createElement('script'),
                    config = {attributes: {}},
                    capturedHandler;

                loadPaypalScript('https://www.paypal.com/sdk/js', {});
                capturedHandler = require.s.contexts._.config.onNodeCreated;

                expect(function () {
                    capturedHandler(node, config, 'paypalSdk');
                }).not.toThrow();
            });

            it('still applies PayPal data attributes to the node', function () {
                var dataAttributes = {'data-namespace': 'paypal', 'data-client-id': 'test'},
                    node = document.createElement('script'),
                    config = {attributes: {paypalSdk: dataAttributes}},
                    capturedHandler;

                loadPaypalScript('https://www.paypal.com/sdk/js', dataAttributes);
                capturedHandler = require.s.contexts._.config.onNodeCreated;
                capturedHandler(node, config, 'paypalSdk');

                expect(node.getAttribute('data-namespace')).toBe('paypal');
                expect(node.getAttribute('data-client-id')).toBe('test');
            });
        });
    });
});
