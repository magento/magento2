/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Checkout/js/model/default-post-code-resolver'
], function (_, DefaultPostCodeResolver) {
    'use strict';

    describe('checkout/js/model/default-post-code-resolver', function () {
        var defaultPostCodeResolver;
        beforeEach(function () {
            defaultPostCodeResolver = DefaultPostCodeResolver;
            window.checkoutConfig = {
                defaultPostcode: '19800'
            };
        });

        it('resolve', function () {
            expect(defaultPostCodeResolver.resolve()).toBeUndefined();
        });
        it('resolve with using default code', function () {
            defaultPostCodeResolver.setUseDefaultPostCode(true);
            expect(defaultPostCodeResolver.resolve()).toEqual('19800');
        });
    });

});
