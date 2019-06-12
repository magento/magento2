/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'mage/backend/tree-suggest'
], function ($) {
    'use strict';

    describe('mage/backend/tree-suggest', function () {
        var treeSuggestSelector = '#tree-suggest';

        beforeEach(function () {
            var $treeSuggest = $('<input name="test-tree-suggest" id="tree-suggest" />');

            $('body').append($treeSuggest);
        });

        afterEach(function () {
            $(treeSuggestSelector).remove();
            $(treeSuggestSelector).treeSuggest('destroy');
        });

        it('Check that treeSuggest inited', function () {
            var $treeSuggest = $(treeSuggestSelector).treeSuggest(),
                treeSuggestInstance = $treeSuggest.data('mage-treeSuggest');

            expect($treeSuggest.is(':mage-treeSuggest')).toBe(true);
            expect(treeSuggestInstance.widgetEventPrefix).toBe('suggest');
        });

        it('Check treeSuggest filter', function () {
            var treeSuggestInstance = $(treeSuggestSelector).treeSuggest().data('mage-treeSuggest'),
                uiHash = {
                    item: {
                        id: 1,
                        label: 'Test Label'
                    }
                };

            expect(treeSuggestInstance._filterSelected(
                [uiHash.item],
                {
                    _allShown: true
                }
            )).toEqual([uiHash.item]);
        });
    });
});
