/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($) {
    'use strict';

    describe('ui/js/modal/alert', function () {
        var element = $('<div>some element</div>'),
            alert = element.alert({});

        it('Check for modal definition', function () {
            expect(alert).toBeDefined();
        });
        it('Show/hide function check', function () {
            expect(element.trigger('openModal')).toBe(element);
            expect(element.trigger('closeModal')).toBe(element);
        });
        it('Integration: modal created on page', function () {
            expect(alert.length).toEqual(1);
        });
    });
});
