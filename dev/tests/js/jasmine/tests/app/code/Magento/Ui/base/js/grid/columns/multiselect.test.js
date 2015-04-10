/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'underscore',
    'Magento_Ui/js/grid/columns/multiselect'
], function (ko, $, _, Multiselect) {
    'use strict';

    describe('ui/js/grid/columns/multiselect', function () {
        var observable,
            element,
            multiSelect,
            items;

        beforeEach(function () {
            element    = $('<input />');
            observable = ko.observable();
            multiSelect = new Multiselect({
                rows: [{
                    id: 1
                }],
                indexField: 'id',
                dataScope: 'scope',
                provider: 'provider'
            });

            $(document.body).append(element);

            ko.applyBindingsToNode(element[0], { datepicker: observable });
        });

        afterEach(function () {
            element.remove();
        });

        it('Default state - Deselected all', function () {
            //items.page(1);
            //multiSelect.selectPage();
            expect(multiSelect.allSelected())
            expect(multiSelect).toBe();
            expect(element).toBeDefined();
        });

        it('select specific several rows on several pages', function () {
            expect(element).toBeDefined();
        });
    });
});