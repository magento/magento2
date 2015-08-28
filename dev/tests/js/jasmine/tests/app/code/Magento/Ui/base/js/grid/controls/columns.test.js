/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/controls/columns'
], function (_, Columns) {
    'use strict';

    describe('ui/js/grid/controls/columns', function () {
        var columnsInstance,
            FakeElement;

        beforeEach(function () {
            columnsInstance = new Columns({
                elems: [],
                index: 'index',
                name: 'name',
                indexField: 'id',
                dataScope: 'scope',
                provider: 'provider'
            });
            FakeElement = function(){
                return this;
            };
            FakeElement.prototype.visible = function(){
                return true;
            };
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
            columnsInstance.elems.push(new FakeElement());
            expect(columnsInstance.isDisabled(columnsInstance.elems()[0])).toBeTruthy();
        });
    });
});
