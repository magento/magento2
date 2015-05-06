/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/filters/filters'
], function (_, Filters) {
    'use strict';

    describe('ui/js/grid/filters/filters', function () {
        var filter;

        beforeEach(function () {
            filter = new Filters({
                elems: [],
                index: 'index',
                name: 'name',
                indexField: 'id',
                dataScope: 'scope',
                provider: 'provider'
            });
        });

        it('Default state - Select no fields', function () {
            filter.elems.push({id:1});
            filter.elems.push({id:2});
            filter.elems.push({id:3});

            expect(filter.allSelected()).toBeFalsy();
        });
    });
});