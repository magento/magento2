/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Checkout/js/action/redirect-on-success', function () {
        var injector = new Squire(),
            RedirectOnSuccess,
            FullScreenLoader;

        beforeEach(function (done) {
            window.checkoutConfig = {
                defaultSuccessPageUrl: 'checkout/success'
            };
            injector.require(['Magento_Checkout/js/action/redirect-on-success',
                'Magento_Checkout/js/model/full-screen-loader'], function (action, loader) {
                RedirectOnSuccess = action;
                FullScreenLoader = loader;
                spyOn(FullScreenLoader, 'startLoader');
                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {}
        });

        it('Checks if loader is called before redirect to success page.', function () {
            spyOn(RedirectOnSuccess, 'redirectToSuccessPage').and.callFake(function () {});
            RedirectOnSuccess.execute();

            expect(FullScreenLoader.startLoader).toHaveBeenCalled();
        });
    });
});
