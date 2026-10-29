/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_Catalog/js/product/weight-handler'
], function ($, weightHandler) {
    'use strict';

    describe('Magento_Catalog/js/product/weight-handler move.tabs binding', function () {
        var instance, $form, $panel;

        beforeEach(function () {
            instance = $.extend(true, {}, weightHandler);
            $form = $('<form id="attributes-edit-form"><div id="tabs-panel"></div></form>');
            $panel = $form.find('#tabs-panel');
            $form.appendTo('body');
        });

        afterEach(function () {
            $(document).off('move.tabs');
            $form.remove();
            instance = null;
        });

        it('calls handlers when move.tabs fires inside the form', function () {
            spyOn(instance, 'hasWeightSwitcher').and.returnValue(true);
            spyOn(instance, 'hasWeightChangeToggle').and.returnValue(true);
            spyOn(instance, 'switchWeight');
            spyOn(instance, 'toggleSwitcher');

            instance.bindAll();
            $panel.trigger('move.tabs');

            expect(instance.hasWeightSwitcher).toHaveBeenCalled();
            expect(instance.hasWeightChangeToggle).toHaveBeenCalled();
            expect(instance.switchWeight).toHaveBeenCalled();
            expect(instance.toggleSwitcher).toHaveBeenCalled();
        });

        it('skips handlers when move.tabs fires outside the form', function () {
            var $outside = $('<div id="outside-panel"></div>').appendTo('body');

            spyOn(instance, 'hasWeightSwitcher');
            spyOn(instance, 'hasWeightChangeToggle');
            spyOn(instance, 'switchWeight');
            spyOn(instance, 'toggleSwitcher');

            instance.bindAll();
            $outside.trigger('move.tabs');

            expect(instance.hasWeightSwitcher).not.toHaveBeenCalled();
            expect(instance.hasWeightChangeToggle).not.toHaveBeenCalled();
            expect(instance.switchWeight).not.toHaveBeenCalled();
            expect(instance.toggleSwitcher).not.toHaveBeenCalled();

            $outside.remove();
        });
    });
});
