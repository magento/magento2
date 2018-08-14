/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire',
    'jquery'
], function (Squire) {
    'use strict';

    describe('Magento_Braintree/js/paypal/button', function () {
        var injector,
            mocks,
            braintree,
            component,
            registry,
            btnId = 'braintree_paypal_btn',
            tplElement = jQuery('<button id="' + btnId + '"></button>')[0];

        require.config({
            map: {
                '*': {
                    'braintree': 'braintree'
                }
            }
        });

        injector = new Squire();
        mocks = {
            'braintree': {
                paypal: {
                    /** Stub */
                    initAuthFlow: function () {}
                },

                /** Stub */
                setup: function () {}
            }
        };

        beforeEach(function (done) {
            injector.mock(mocks);

            injector.require([
                'braintree',
                'uiRegistry',
                'Magento_Braintree/js/paypal/button'
            ], function (adapter, reg, Constr) {
                braintree = adapter;
                registry = reg;
                jQuery(document.body).append(tplElement);

                spyOn(braintree, 'setup').and.callFake(function () {
                    registry.set('braintreePaypal.currentIntegration', braintree);
                    jQuery('#' + btnId).removeAttr('disabled');
                });

                component = new Constr({
                    id: btnId
                });
                done();
            });
        });

        afterAll(function (done) {
            tplElement.remove();
            registry.remove(component.integrationName);
            done();
        });

        it('The PayPal::initAuthFlow throws an exception.', function () {
            var $selector = jQuery('#' + component.id);

            spyOn(braintree.paypal, 'initAuthFlow').and.callFake(function () {
                throw new TypeError('Cannot read property of undefined');
            });

            $selector.trigger('click');

            expect($selector.prop('disabled')).toEqual(true);
        });
    });
});
