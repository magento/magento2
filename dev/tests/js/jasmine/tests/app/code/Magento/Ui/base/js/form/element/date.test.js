/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'Magento_Ui/js/form/element/date'
], function (DateElement) {
    'use strict';

    describe('Magento_Ui/js/form/element/date', function () {
        var params, model;

        beforeEach(function () {
            params = {
                dataScope: 'abstract'
            };
            model = new DateElement(params);
        });

        describe('getInitialValue method', function () {
            it('check for default', function () {
                expect(model.getInitialValue()).toEqual('');
            });
            it('check with default value', function () {
                model.default = 1;
                expect(model.getInitialValue()).toEqual('01/01/1970');
            });
            it('check with value', function () {
                model.value(1);
                expect(model.getInitialValue()).toEqual('01/01/1970');
            });
            it('check with value and default', function () {
                model.default = 1;
                model.value(0);
                expect(model.getInitialValue()).toEqual(0);
            });
        });
        describe('initProperties method', function () {
            it('check for chainable', function () {
                expect(model.initProperties()).toEqual(model);
            });
            it('check for extend', function () {
                model.initProperties();
                expect(model.dateFormat).toBeDefined();
            });
        });
    });
});
