/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/confirm'
], function ($) {
    'use strict';

    describe('ui/js/modal/confirm', function () {

        var widget,
            element,
            confirm;

        beforeEach(function () {
            element = $('<div id="element">some element</div>');
            confirm = element.confirm({});
            widget = element.confirm({}).data('mage-confirm');
        });

        afterEach(function () {
            $('element').remove();

        });

        it('Check for modal definition', function () {
            expect(confirm).toBeDefined();
        });
        it('Show/hide function check', function () {
            expect(element.trigger('openModal')).toBe(element);
            expect(element.trigger('closeModal')).toBe(element);
        });
        it('Integration: modal created on page', function () {
            expect(confirm.length).toEqual(1);
        });
        it('Check confirm class button', function () {
            var expectedClassResult = 'action primary action-primary action-accept',
                expectedClass = widget.options.buttons[1].class;

            expect($(expectedClass).selector).toContain(expectedClassResult);
        });
    });
});
