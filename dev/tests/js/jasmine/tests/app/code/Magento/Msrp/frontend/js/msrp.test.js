/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Msrp/js/msrp'
], function ($) {
    'use strict';

    describe('Testing addToCart Widget', function () {
        var wdContainer;

        beforeEach(function () {
            wdContainer = $('<div />');
        });

        afterEach(function () {
            $(wdContainer).remove();
        });

        it('widget extends jQuery object', function () {
            expect($.fn.addToCart).toBeDefined();
        });

        it('widget gets options', function () {
            wdContainer.addToCart({
                'cartButtonId': 'FAKE_ID'
            });
            expect(wdContainer.addToCart('option', 'cartButtonId')).toBe('FAKE_ID');
        });

        it('widget tries to submit Cart Form on click', function (done) {
            var link = $('<a />');
            wdContainer.addToCart({
                'cartButtonId': link
            }).on('addToCart', function (event, result) {
                expect(result).toEqual(wdContainer[0]);
                done();
            });
            link.click();
        });
    });
});
