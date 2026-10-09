/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/* eslint max-nested-callbacks: 0 */
define([
    'squire',
    'underscore',
    'ko'
], function (Squire, _, ko) {
    'use strict';

    describe('Magento_Checkout/js/view/payment/list', function () {
        var injector = new Squire(),
            paymentMethods,
            rendererList,
            layoutSpy,
            List,
            defaultGroup = {
                alias: 'default',
                displayArea: 'defaultGroupArea',
                sortOrder: 0
            };

        beforeEach(function (done) {
            paymentMethods = ko.observableArray([]);
            rendererList = ko.observableArray([
                {
                    type: 'checkmo',
                    component: 'Magento_OfflinePayments/js/view/payment/method-renderer/checkmo-method'
                },
                {
                    type: 'cashondelivery',
                    component: 'Magento_OfflinePayments/js/view/payment/method-renderer/cashondelivery-method'
                }
            ]);
            layoutSpy = jasmine.createSpy('layout');

            injector.mock({
                'Magento_Checkout/js/model/payment/method-list': paymentMethods,
                'Magento_Checkout/js/model/payment/renderer-list': rendererList,

                // 'uiLayout'/'uiRegistry' are aliases (see Magento_Ui's requirejs-config.js
                // 'map' config) that resolve to these real module ids before Squire's mocking
                // hook sees them, so the mock has to target the resolved ids directly.
                'Magento_Ui/js/core/renderer/layout': layoutSpy,
                'Magento_Ui/js/lib/registry/registry': {
                    /** Stub that immediately resolves the default payment method group. */
                    get: function (name, callback) {
                        if (callback) {
                            callback(name === 'methodGroup' ? defaultGroup : undefined);
                        }
                    },

                    /** Stub, unused by the code under test. */
                    async: function () {
                        return function () {};
                    },

                    /** Stub, unused by the code under test. */
                    remove: function () {},

                    /** Stub, unused by the code under test. */
                    set: function () {}
                },
                'Magento_Checkout/js/model/checkout-data-resolver': {
                    resolvePaymentMethod: jasmine.createSpy('resolvePaymentMethod')
                }
            });

            injector.require(['Magento_Checkout/js/view/payment/list'], function (Constr) {
                List = Constr;
                done();
            });
        });

        afterEach(function () {
            try {
                injector.remove();
                injector.clean();
            } catch (e) { // eslint-disable-line no-unused-vars
            }
        });

        /**
         * Returns the components passed to every layout() call so far that correspond
         * to an actual payment method renderer (as opposed to the default group node).
         *
         * @returns {Array}
         */
        function getRendererLayoutCalls() {
            return layoutSpy.calls.allArgs()
                .map(function (args) {
                    return args[0][0];
                })
                .filter(function (component) {
                    return Boolean(component.item);
                });
        }

        it('assigns each renderer the current rank of its method within the payment method list, ' +
            'so a method with a lower sort order that becomes available later is inserted ahead of ' +
            'methods that were already rendered', function () {
            var codLayoutCall;

            new List({
                name: 'test.payment.list'
            });

            // Only Check/Money Order is applicable while the shipping country is United States.
            paymentMethods([
                {method: 'checkmo', title: 'Check / Money Order'}
            ]);

            layoutSpy.calls.reset();

            // The shipping country changes to Germany: Cash On Delivery (sort_order 5) becomes
            // applicable and is returned ahead of Check/Money Order (sort_order 10).
            paymentMethods([
                {method: 'cashondelivery', title: 'Cash On Delivery'},
                {method: 'checkmo', title: 'Check / Money Order'}
            ]);

            getRendererLayoutCalls().forEach(function (component) {
                var expectedSortOrder = _.pluck(paymentMethods(), 'method').indexOf(component.item.method);

                expect(component.sortOrder).toBe(expectedSortOrder);
            });

            codLayoutCall = _.find(getRendererLayoutCalls(), function (component) {
                return component.item.method === 'cashondelivery';
            });

            expect(codLayoutCall).toBeDefined();
            expect(codLayoutCall.sortOrder).toBe(0);
        });

        it('assigns consecutive sort orders to every renderer contributed by a single payment method, ' +
            'so a method rendered by more than one renderer (e.g. Magento_Vault, one renderer per stored ' +
            'token) keeps its renderers in their original relative order instead of reversing them', function () {
            var vaultLayoutCalls;

            rendererList([
                {
                    type: 'checkmo',
                    component: 'Magento_OfflinePayments/js/view/payment/method-renderer/checkmo-method'
                },
                {
                    type: 'vault_0',
                    component: 'Magento_Vault/js/view/payment/method-renderer/vault',

                    /** Stub vault type comparator: vault token renderers share the method's base name. */
                    typeComparatorCallback: function (typeA, typeB) {
                        return typeA.substring(0, typeA.lastIndexOf('_')) === typeB;
                    }
                },
                {
                    type: 'vault_1',
                    component: 'Magento_Vault/js/view/payment/method-renderer/vault',

                    /** Stub vault type comparator: vault token renderers share the method's base name. */
                    typeComparatorCallback: function (typeA, typeB) {
                        return typeA.substring(0, typeA.lastIndexOf('_')) === typeB;
                    }
                },
                {
                    type: 'vault_2',
                    component: 'Magento_Vault/js/view/payment/method-renderer/vault',

                    /** Stub vault type comparator: vault token renderers share the method's base name. */
                    typeComparatorCallback: function (typeA, typeB) {
                        return typeA.substring(0, typeA.lastIndexOf('_')) === typeB;
                    }
                }
            ]);

            new List({
                name: 'test.payment.list'
            });

            paymentMethods([
                {method: 'checkmo', title: 'Check / Money Order'},
                {method: 'vault', title: 'Stored Cards'}
            ]);

            vaultLayoutCalls = getRendererLayoutCalls().filter(function (component) {
                return component.item.method === 'vault';
            });

            expect(vaultLayoutCalls.length).toBe(3);

            // checkmo is the sole renderer of the preceding method, so vault's renderers start at
            // sort order 1 and increase in the same order the renderers were declared, not reversed.
            expect(_.pluck(vaultLayoutCalls, 'name')).toEqual(['vault_0', 'vault_1', 'vault_2']);
            expect(_.pluck(vaultLayoutCalls, 'sortOrder')).toEqual([1, 2, 3]);
        });
    });
});
