/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($) {
    'use strict';

    describe('ui/js/modal/modal', function () {

        var element,
            modal;

        beforeEach(function () {
            element = $('<div id="element">Element</div>');
            modal = element.modal({}).data('mage-modal');

            $(element).append('<h1 class="modal-title"' +
                ' data-role="title">Title</h1>' +
                '<span class="modal-subtitle"' +
                ' data-role="subTitle"></span>');
        });

        afterEach(function () {
            $('.modal-title').remove();
            $('#element').remove();

        });

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

        it('Verify set title', function () {
            var newTitle = 'New modal title';

            modal.setTitle(newTitle);
            expect($(modal.options.modalTitle).text()).toContain(newTitle);
            expect($(modal.options.modalTitle).find(modal.options.modalSubTitle).length).toBe(1);
        });
    });
});
