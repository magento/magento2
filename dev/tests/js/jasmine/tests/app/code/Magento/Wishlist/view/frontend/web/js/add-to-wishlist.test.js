/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Wishlist/js/add-to-wishlist'
], function ($) {
    'use strict';

    describe('Testing addToWishlist widget', function () {
        var wdContainer;

        beforeEach(function () {
            wdContainer = $('<input type="hidden" class="bundle-option-11  product bundle option" \n' +
                'name="bundle_option[11]" value="15" aria-required="true"/>');
        });

        afterEach(function () {
            $(wdContainer).remove();
        });

        it('widget extends jQuery object', function () {
            expect($.fn.addToWishlist).toBeDefined();
        });

        it('widget gets options', function () {
            wdContainer.addToWishlist({
                'bundleInfo': 'test'
            });
            expect(wdContainer.addToWishlist('option', 'bundleInfo')).toBe('test');
        });
    });
});
