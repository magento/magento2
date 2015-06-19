/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($) {
    'use strict';

    describe('ui/js/modal/modal', function () {
        var element = $('<div>some element</div>'),
            modal = element.modal({}).data('mage-modal');

        it('Check for modal definition', function () {
            expect(modal).toBeDefined();
        });
        it('Show/hide function check', function () {
            expect(element.trigger('openDialog')).toBe(element);
            expect(element.trigger('closeDialog')).toBe(element);
        });
        it('Check for transition support', function () {
            expect(modal.whichTransitionEvent()).toBe('webkitTransitionEnd');
        });
    });
});