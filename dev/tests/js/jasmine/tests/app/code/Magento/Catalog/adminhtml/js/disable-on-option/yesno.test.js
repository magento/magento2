/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

define(['Magento_Catalog/js/components/disable-on-option/yesno'], function (YesNo) {
    'use strict';

    var model;

    describe('Magento_Catalog/js/components/disable-on-option/yesno', function () {
        beforeEach(function () {
            model = new YesNo({
                name: 'dynamic_rows',
                dataScope: '',
                value: 12,
                visible: true,
                disabled: false

            });
        });

        it('Verify initial value', function () {
            expect(model.get('value')).toBe(12);
        });
        it('Verify value when element becomes invisible', function () {
            model.set('visible', false);
            expect(model.get('value')).toBe(0);
        });
        it('Verify value when element becomes disabled', function () {
            model.set('disabled', false);
            expect(model.get('value')).toBe(12);
        });
        it('Verify value when element becomes invisable and disabled', function () {
            model.set('disabled', true);
            model.set('visible', false);
            expect(model.get('value')).toBe(0);
        });
    });
});
