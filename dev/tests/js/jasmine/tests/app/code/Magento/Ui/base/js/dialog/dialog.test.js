/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/dialog/dialog'
], function ($) {
    'use strict';

    describe('ui/js/dialog/dialog', function () {
        var element = $('<div>some element</div>'),
            dialog = element.dialog({}).data('mage-dialog');

        it('Check for dialog definition', function () {
            expect(dialog).toBeDefined();
        });
        it('Show/hide function check', function () {
            expect(element.trigger('openDialog')).toBe(element);
            expect(element.trigger('closeDialog')).toBe(element);
        });
        it('Check for transition support', function () {
            expect(dialog.whichTransitionEvent()).toBe('webkitTransitionEnd');
        });
    });
});