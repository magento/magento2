/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Ui/js/modal/modal-component'
], function (ModalComponent) {
    'use strict';

    describe('Magento_Ui/js/modal/modal-component', function () {
        var component;

        beforeEach(function () {
            component = new ModalComponent({
                name: 'test_form.test_form.modal'
            });
        });

        it('exposes "visible" as an observable defaulting to true', function () {
            expect(typeof component.visible).toBe('function');
            expect(component.visible()).toBe(true);
        });

        it('allows a parent dynamic row record to toggle its visibility', function () {
            component.visible(false);

            expect(component.visible()).toBe(false);
        });
    });
});
