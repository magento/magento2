/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Backend/js/validate-store'
], function ($, storeValidation) {
    'use strict';

    describe('Magento_Backend/js/validate-store', function () {
        var form,
            model;

        beforeEach(function () {
            form = $('<form />');
            model = new storeValidation();
        });

        it('widget extends jQuery object', function () {
            expect($.fn.storeValidation).toBeDefined();
        });

        it('Check options setting', function () {
            form.storeValidation();
            expect(form.storeValidation('option', 'storeData')).toBe(null);

            form.storeValidation({'storeData' : 'test1'});
            expect(form.storeValidation('option', 'storeData')).toBe('test1');
        });

        it('_needConfirm method', function () {
            expect(model._needConfirm()).toEqual(true);
        });
    });
});
