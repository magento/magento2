/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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

        it('Closing a modal that was never opened does not throw', function () {
            // Magento_Checkout/js/model/sidebar hide() closes the order-summary modal even
            // on viewports where it was never opened, so overlay is still undefined.
            expect(modal.overlay).toBeUndefined();
            expect(function () {
                modal._destroyOverlay();
            }).not.toThrow();
        });

        it('Closing an already closed modal does not throw', function () {
            modal.openModal();
            modal.closeModal();

            // closeModal() defers _close() to the transitionEvent, and every call queues
            // another handler, so a close that races the closing animation - a double
            // click on the close button, for instance - runs _close() twice.
            modal._close();
            expect(modal.overlay).toBeNull();

            expect(function () {
                modal._close();
            }).not.toThrow();
        });

        it('Closing twice tears the overlay down exactly once', function () {
            modal.openModal();
            modal.closeModal();
            modal._close();
            modal._close();

            expect(modal.overlay).toBeNull();
            expect($('.' + modal.options.overlayClass).length).toBe(0);
        });

        it('Verify set title', function () {
            var newTitle = 'New modal title';

            modal.setTitle(newTitle);
            expect($(modal.options.modalTitle).text()).toContain(newTitle);
            expect($(modal.options.modalTitle).find(modal.options.modalSubTitle).length).toBe(1);
        });
    });
});
