/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/search/search'
], function (Search) {
    'use strict';

    describe('Magento_Ui/js/grid/search/search', function () {
        var searchObj,
            temp;

        beforeEach(function () {
            searchObj = new Search();
        });
        it('has initialized', function () {
            expect(searchObj).toBeDefined();
        });
        it('has initObservable', function () {
            temp = searchObj.initObservable();
            expect(temp).toBeDefined();
        });
        it('has initObservable', function () {
            spyOn(searchObj, 'initChips');
            searchObj.initChips();
            expect(searchObj.initChips).toHaveBeenCalled();
        });
        it('has initChips', function () {
            spyOn(searchObj, 'chips');
            searchObj.initChips();
            expect(searchObj.chips).toHaveBeenCalled();
        });
        it('has updatePreview', function () {
            spyOn(searchObj, 'updatePreview');
            searchObj.updatePreview();
            expect(searchObj.updatePreview).toHaveBeenCalled();
        });
    });
});
