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

        describe('initConfig method', function () {
            it('check for chainable', function () {
                expect(model.initConfig()).toEqual(model);
            });
            it('check for extend', function () {
                model.initConfig();
                expect(model.dateFormat).toBeDefined();
            });
        });
    });
});
