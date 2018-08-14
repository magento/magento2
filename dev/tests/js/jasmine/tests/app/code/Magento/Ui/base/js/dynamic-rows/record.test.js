/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'Magento_Ui/js/dynamic-rows/record'
], function (Record) {
    'use strict';

    var model,
        parentComponent = {
            setMaxPosition: jasmine.createSpy(),
            maxPosition: 10
        };

    /**
     * Run before each test method
     *
     * @return void
     */
    beforeEach(function () {
        model = new Record({});
        model.parentComponent = jasmine.createSpy().and.returnValue(parentComponent);
    });

    /**
     * Testing 'Magento_Ui/js/dynamic-rows/record' class
     *
     * @return void
     */
    describe('Magento_Ui/js/dynamic-rows/record', function () {
        it('Calls "initPosition" method without position.', function () {
            model.initPosition(false);

            expect(model.parentComponent().setMaxPosition).toHaveBeenCalledWith(NaN, model);
            expect(model.position).toEqual(10);
        });

        it('Calls "initPosition" method without position 0.', function () {
            model.position = 10;
            model.initPosition(0);

            expect(model.parentComponent().setMaxPosition).toHaveBeenCalledWith(0, model);
            expect(model.position).toEqual(10);
        });
    });
});
