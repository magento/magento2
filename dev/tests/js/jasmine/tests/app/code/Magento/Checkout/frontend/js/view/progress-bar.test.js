/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */
define([
    'squire'
], function (Squire) {
    'use strict';

    var injector = new Squire(),
        billingAddress = jasmine.createSpyObj('billingAddress',['needCancelBillingAddressChanges']),
        mocks = {
            'Magento_Checkout/js/view/billing-address': jasmine.createSpy().and.returnValue(
                billingAddress
            )
        },
        progressbar;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/view/progress-bar'], function (Constr) {
            progressbar = new Constr;
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Magento_Checkout/js/view/progress-bar', function () {
        describe('"navigateTo" method', function () {
            it('Test needCancelBillingAddressChanges negative scenario', function () {
                var step = {
                    code: 'billing'
                };
                progressbar.navigateTo(step);
                expect(billingAddress.needCancelBillingAddressChanges).not.toHaveBeenCalled();
            });

            it('Test if needCancelBillingAddressChanges is called when navigated to the shipping step', function () {
                var step = {
                    code: 'shipping'
                };
                progressbar.navigateTo(step);
                expect(billingAddress.needCancelBillingAddressChanges).toHaveBeenCalled();
            });
        });
    });
});
