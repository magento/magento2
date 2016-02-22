/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'Magento_Ui/js/form/element/boolean'
], function (BooleanElement) {
    'use strict';

    describe('Magento_Ui/js/form/element/boolean', function () {
        var params, model;

        beforeEach(function () {
            params = {
                dataScope: 'abstract'
            };
            model = new BooleanElement(params);
        });

        describe('getInitialValue method', function () {
            it('check for default', function () {
                expect(model.getInitialValue()).toEqual(false);
            });
            it('check with default value', function () {
                model.default = 1;
                expect(model.getInitialValue()).toEqual(false);
            });
            it('check with value', function () {
                model.value(1);
                expect(model.getInitialValue()).toEqual(true);
            });
            it('check with value and default', function () {
                model.default = 1;
                model.value(0);
                expect(model.getInitialValue()).toEqual(false);
            });
        });
        describe('onUpdate method', function () {
            it('check for setUnique call', function () {
                spyOn(model, 'setUnique');
                model.hasUnique = true;
                model.onUpdate();
                expect(model.setUnique).toHaveBeenCalled();
            });
            it('check for setUnique not call', function () {
                spyOn(model, 'setUnique');
                model.onUpdate();
                expect(model.setUnique).not.toHaveBeenCalled();
            });
        });
    });
});
