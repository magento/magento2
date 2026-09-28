/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/confirm'
], function ($) {
    'use strict';

    describe('ui/js/modal/confirm', function () {
        var element = $('<div>some element</div>'),
            confirm = element.confirm({});

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
    });
});
