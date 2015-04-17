/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/columns/multiselect'
], function (_, Columns) {
    'use strict';

    describe('ui/js/grid/controls/columns', function () {
        var columnsInstance;

        beforeEach(function () {
            columnsInstance = new Columns({
                elems: [],
                index: 'index',
                name: 'name',
                indexField: 'id',
                dataScope: 'scope',
                provider: 'provider'
            });
            columnsInstance.source = {
                set: function () {}
            };
            spyOn(columnsInstance.source, 'set');
        });

        it('hasOverflow method', function () {
            columnsInstance.viewportSize = 2;
            columnsInstance.elems.push({id:1});
            columnsInstance.elems.push({id:2});

            expect(columnsInstance.hasOverflow()).toBeFalsy();

            columnsInstance.elems.push({id:3});
            expect(columnsInstance.hasOverflow()).toBeTruthy();
        });

        it('isDisabled method', function () {
            columnsInstance.viewportMaxSize = 4;
            columnsInstance.elems.push({id:1});
            expect(columnsInstance.isDisabled()).toBeFalsy();

            columnsInstance.elems.push({id:2});
            expect(columnsInstance.isDisabled()).toBeTruthy();

            columnsInstance.elems.push({id:3});
            expect(columnsInstance.isDisabled()).toBeTruthy();

            columnsInstance.elems.push({id:4});
            expect(columnsInstance.isDisabled()).toBeTruthy();

            columnsInstance.elems.push({id:5});
            expect(columnsInstance.isDisabled()).toBeFalsy();
        });

    });
});