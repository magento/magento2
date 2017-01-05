/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            expect(element.trigger('openModal')).toBe(element);
            expect(element.trigger('closeModal')).toBe(element);
        });
        it('Integration: modal created on page', function () {
            expect($(modal).length).toEqual(1);
        });
    });
});
