/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/prompt'
], function ($) {
    'use strict';

    describe('ui/js/modal/prompt', function () {
        var element = $('<div>some element</div>'),
            prompt = element.prompt({});

        it('Check for modal definition', function () {
            expect(prompt).toBeDefined();
        });
        it('Show/hide function check', function () {
            expect(element.trigger('openModal')).toBe(element);
            expect(element.trigger('closeModal')).toBe(element);
        });
        it('Integration: modal created on page', function () {
            expect(prompt.length).toEqual(1);
        });
    });
});
