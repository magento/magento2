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
        it('set the proper keywordUpdated value on new search keyword', function () {
            searchObj.value = 'keyword 1';
            expect(searchObj.keywordUpdated).toEqual(false);
            searchObj.apply('keyword 2');
            expect(searchObj.keywordUpdated).toEqual(true);
            searchObj.apply('keyword 2');
            expect(searchObj.keywordUpdated).toEqual(false);
            searchObj.apply('keyword 3');
            expect(searchObj.keywordUpdated).toEqual(true);
        });
    });
});
