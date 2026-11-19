/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/* eslint max-nested-callbacks: 0 */
define([
    'squire',
    'ko',
    'jquery'
], function (Squire, ko, $) {
    'use strict';

    var injector = new Squire(),
        requests,
        sentBilling,
        selectedBillingKey,
        quote = {
            billingAddress: ko.observable(null),
            shippingAddress: ko.observable(null),
            shippingMethod: ko.observable({
                'method_code': 'flatrate',
                'carrier_code': 'flatrate'
            }),
            setTotals: jasmine.createSpy('setTotals')
        },
        errorProcessor = jasmine.createSpyObj('errorProcessor', ['process']),
        rejected = jasmine.createSpyObj('rejectedBillingAddresses', ['add']),
        messageList = jasmine.createSpyObj('messageList', ['addErrorMessage']),
        processor,
        loader = jasmine.createSpyObj('loader', ['startLoader', 'stopLoader']),
        extendPayload,
        shipping,
        defaultBilling;

    /**
     * @param {String} key
     * @param {*} customerAddressId
     * @param {Boolean} isDefaultBilling
     * @return {Object}
     */
    function savedAddress(key, customerAddressId, isDefaultBilling) {
        return {
            customerAddressId: customerAddressId,

            /** Stub */
            isDefaultBilling: function () {
                return !!isDefaultBilling;
            },

            /** Stub */
            getCacheKey: function () {
                return key;
            },

            /** Stub */
            canUseForBilling: function () {
                return true;
            }
        };
    }

    /**
     * Answer the next shipping information request.
     *
     * @param {Boolean} accept
     * @param {Number} status
     */
    function answer(accept, status) {
        var request = requests.shift();

        if (accept) {
            request.resolve({
                totals: {},
                'payment_methods': []
            });
        } else {
            request.reject({
                status: status
            });
        }
    }

    beforeEach(function (done) {
        requests = [];
        sentBilling = [];
        selectedBillingKey = null;
        shipping = savedAddress('customer-address1', 1);
        defaultBilling = savedAddress('customer-address2', 2, true);
        quote.shippingAddress(shipping);
        quote.billingAddress(defaultBilling);
        errorProcessor.process.calls.reset();
        rejected.add.calls.reset();
        messageList.addErrorMessage.calls.reset();
        loader.startLoader.calls.reset();
        loader.stopLoader.calls.reset();
        extendPayload = function () {};

        injector.mock({
            'Magento_Checkout/js/model/quote': quote,
            'Magento_Checkout/js/model/resource-url-manager': {
                /** Stub */
                getUrlForSetShippingInformation: function () {
                    return 'shipping-information';
                }
            },
            'mage/storage': {
                /** Stub */
                post: function (url, body) {
                    var request = $.Deferred();

                    sentBilling.push(JSON.parse(body).addressInformation['billing_address'].customerAddressId);
                    requests.push(request);

                    return request;
                }
            },
            'Magento_Checkout/js/model/payment-service': jasmine.createSpyObj('paymentService', ['setPaymentMethods']),
            'Magento_Checkout/js/model/payment/method-converter': function () {
                return [];
            },
            'Magento_Checkout/js/model/error-processor': errorProcessor,
            'Magento_Checkout/js/model/full-screen-loader': loader,
            'Magento_Checkout/js/action/select-billing-address': function (address) {
                quote.billingAddress(address);
            },
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': function (payload) {
                extendPayload(payload);
            },
            'Magento_Checkout/js/checkout-data': {
                /** Stub */
                getSelectedBillingAddress: function () {
                    return selectedBillingKey;
                }
            },
            'Magento_Checkout/js/model/rejected-billing-addresses': rejected,
            'Magento_Ui/js/model/messageList': messageList
        });
        injector.require(['Magento_Checkout/js/model/shipping-save-processor/default'], function (instance) {
            processor = instance;
            done();
        });
    });

    describe('Magento_Checkout/js/model/shipping-save-processor/default', function () {
        it('saves once when the shipping information is accepted', function () {
            var resolved = jasmine.createSpy('resolved');

            processor.saveShippingInformation().done(resolved);
            answer(true);

            expect(sentBilling).toEqual([2]);
            expect(resolved).toHaveBeenCalled();
            expect(errorProcessor.process).not.toHaveBeenCalled();
        });

        it('uses the shipping address for billing when the default billing address is rejected (#41445)', function () {
            var resolved = jasmine.createSpy('resolved');

            processor.saveShippingInformation().done(resolved);
            answer(false, 400);
            answer(true);

            expect(sentBilling).toEqual([2, 1]);
            expect(quote.billingAddress()).toBe(shipping);
            expect(rejected.add).toHaveBeenCalledWith(defaultBilling);
            expect(messageList.addErrorMessage).toHaveBeenCalled();
            expect(errorProcessor.process).not.toHaveBeenCalled();
            expect(resolved).toHaveBeenCalled();
        });

        it('shows the first error and keeps the billing address when the retry fails too', function () {
            var failed = jasmine.createSpy('failed');

            processor.saveShippingInformation().fail(failed);
            answer(false, 400);
            answer(false, 400);

            expect(sentBilling).toEqual([2, 1]);
            expect(quote.billingAddress()).toBe(defaultBilling);
            expect(rejected.add).not.toHaveBeenCalled();
            expect(errorProcessor.process).toHaveBeenCalledTimes(1);
            expect(failed).toHaveBeenCalled();
        });

        it('does not retry with a billing address the customer chose', function () {
            selectedBillingKey = 'customer-address2';
            processor.saveShippingInformation();
            answer(false, 400);

            expect(sentBilling).toEqual([2]);
            expect(errorProcessor.process).toHaveBeenCalled();
        });

        it('does not retry when the failure is not a rejection', function () {
            processor.saveShippingInformation();
            answer(false, 500);

            expect(sentBilling).toEqual([2]);
            expect(rejected.add).not.toHaveBeenCalled();
        });

        it('does not retry when billing is already the shipping address', function () {
            quote.billingAddress(shipping);
            processor.saveShippingInformation();
            answer(false, 400);

            expect(sentBilling).toEqual([1]);
        });

        it('does not retry with a saved address that is not the default billing address', function () {
            quote.billingAddress(savedAddress('customer-address3', 3, false));
            processor.saveShippingInformation();
            answer(false, 400);

            expect(sentBilling).toEqual([3]);
            expect(rejected.add).not.toHaveBeenCalled();
        });

        it('stops the loader on every path', function () {
            processor.saveShippingInformation();
            answer(false, 400);
            answer(true);
            processor.saveShippingInformation();
            answer(false, 500);

            expect(loader.startLoader).toHaveBeenCalledTimes(2);
            expect(loader.stopLoader).toHaveBeenCalledTimes(2);
        });

        it('does not start the loader when building the request fails', function () {
            extendPayload = function () {
                throw new Error('extender');
            };

            expect(function () {
                processor.saveShippingInformation();
            }).toThrowError('extender');
            expect(loader.startLoader).not.toHaveBeenCalled();
            expect(sentBilling).toEqual([]);
        });

        it('does not retry with an address entered in checkout', function () {
            quote.billingAddress(savedAddress('new-customer-billing-address', undefined));
            processor.saveShippingInformation();
            answer(false, 400);

            expect(sentBilling).toEqual([undefined]);
        });
    });
});
