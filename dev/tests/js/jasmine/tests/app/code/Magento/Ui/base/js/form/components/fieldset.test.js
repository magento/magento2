/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Ui/js/form/components/fieldset'
], function (Constr) {
    'use strict';

    describe('Magento_Ui/js/form/components/fieldset', function () {
        var obj;

        beforeEach(function () {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: ''
            });
            obj.initObservable();
        });

        it('updates cached errors count on child error change', function () {
            var child = {};

            obj.onChildrenError('Error', child);
            expect(obj._childrenErrorsCount).toBe(1);
            expect(child.__uiHasError).toBe(true);
            expect(obj.error()).toBe(true);

            obj.onChildrenError('', child);
            expect(obj._childrenErrorsCount).toBe(0);
            expect(child.__uiHasError).toBe(false);
            expect(obj.error()).toBeFalsy();
        });

        it('resyncs cached error count when no errors found', function () {
            obj._childrenErrorsCount = 1;

            obj.onChildrenError('', null);
            expect(obj._childrenErrorsCount).toBe(0);
            expect(obj.error()).toBeFalsy();
        });
    });
});
