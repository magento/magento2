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

        /**
         * @param {Number} index
         * @returns {Object}
         * @constructor
         */
        ElementMock = function (index) {
            return {
                /**
                 * @return void
                 */
                destroy: function () {},
                index: index
            };
        };

    /**
     * Run before each test method
     *
     * @return void
     */
    beforeEach(function (done) {
        model = new Record({});
        done();
    });

    /**
     * Testing changePage and delete record methods
     *
     * @return void
     */
    describe('Magento_Ui/js/dynamic-rows/record', function () {
        it('Calls "initPosition" method without position.', function () {
            var parentComponent = {
                setMaxPosition: jasmine.createSpy(),
                maxPosition: 10
            };

            model.parentComponent = jasmine.createSpy().and.returnValue(parentComponent);
            model.initPosition(false);

            expect(model.parentComponent().setMaxPosition).toHaveBeenCalledWith(NaN, model);
            expect(model.position).toEqual(10);
        });

        it('Calls "initPosition" method without position 0.', function () {
            var parentComponent = {
                setMaxPosition: jasmine.createSpy(),
                maxPosition: 0
            };

            model.parentComponent = jasmine.createSpy().and.returnValue(parentComponent);
            model.position = 10;
            model.initPosition(0);

            expect(model.parentComponent().setMaxPosition).toHaveBeenCalledWith(0, model);
            expect(model.position).toEqual(10);
        });
    });
});
