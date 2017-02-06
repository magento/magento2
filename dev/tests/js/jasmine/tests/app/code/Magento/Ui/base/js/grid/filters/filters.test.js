/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/filters/filters'
], function (Filter) {
    'use strict';

    describe('Magento_Ui/js/grid/filters/filters', function () {
        var filterObj,
            temp;

        beforeEach(function () {
            filterObj = new Filter({
                name: 'filter'
            });
        });
        it('has been initialized', function () {
            expect(filterObj).toBeDefined();
        });
        it('has initObservable', function () {
            temp = filterObj.initObservable();
            expect(temp).toBeDefined();
        });
        it('has initElement', function () {
            spyOn(filterObj, 'initElement');
            filterObj.initElement();
            expect(filterObj.initElement).toHaveBeenCalled();
        });
        it('has clear', function () {
            temp = filterObj.clear();
            expect(temp).toBeDefined();
        });
        it('has apply', function () {
            temp = filterObj.apply();
            expect(temp).toBeDefined();
        });
        it('has cancel', function () {
            temp = filterObj.cancel();
            expect(temp).toBeDefined();
        });
        it('has isFilterVisible method', function () {
            temp = {
                visible: function () {
                    return false;
                }
            };
            spyOn(filterObj, 'isFilterActive');
            filterObj.isFilterVisible(temp);
            expect(filterObj.isFilterActive).toHaveBeenCalled();
        });
        it('has isFilterActive method', function () {
            spyOn(filterObj, 'isFilterActive');
            filterObj.isFilterActive();
            expect(filterObj.isFilterActive).toHaveBeenCalled();
        });
        it('has hasVisible method', function () {
            spyOn(filterObj, 'hasVisible');
            filterObj.hasVisible();
            expect(filterObj.hasVisible).toHaveBeenCalled();
        });
    });
});
