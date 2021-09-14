/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/prompt'
], function ($) {
    'use strict';

    describe('ui/js/modal/prompt', function () {

        var element,
            prompt,
            widget;

        beforeEach(function () {
            element = $('<div id="element">some element</div>'),
            prompt = element.prompt({});
            widget = element.prompt({}).data('mage-prompt');
        });

        afterEach(function () {
            $('#element').remove();
        });

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
        it('Check cancel action', function () {
            var cancel = spyOn(widget.options.actions, 'cancel');

            jQuery('.modals-overlay').trigger('click');
            expect(widget.options.outerClickHandler).toBeDefined();
            expect(cancel).toHaveBeenCalled();
        });
    });
});
