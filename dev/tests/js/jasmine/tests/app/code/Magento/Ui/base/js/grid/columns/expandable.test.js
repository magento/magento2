/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'Magento_Ui/js/grid/columns/expandable'
], function (Expandable) {
    'use strict';

    describe('Ui/js/grid/columns/expandable', function () {
        var expandable, record;

        beforeEach(function () {
            expandable = new Expandable({
                index: 'shared_catalog',
                visibeItemsLimit: 1,
                options: []
            });
            record = {
                'entity_id': '3',
                'row_id': '3',
                'shared_catalog': []
            };
        });

        describe('getFullLabel method', function () {
            it('get label while options are empty', function () {
                expect(expandable.getFullLabel(record)).toBe('');
            });

            it('get label after options are set', function () {
                record['shared_catalog'].push(1);
                expandable.options.push({
                    label: 'Default',
                    value: '1'
                });
                expect(expandable.getFullLabel(record)).toBe('Default');
            });

            it('check if getLabelsArray have been called', function () {
                spyOn(expandable, 'getLabelsArray').and.returnValues(['Default', 'Custom']);
                expandable.getFullLabel(record);
                expect(expandable.getLabelsArray).toHaveBeenCalled();
            });
        });

        describe('getShortLabel method', function () {
            it('get label while options are empty', function () {
                expect(expandable.getShortLabel(record)).toBe('');
            });
        });

        describe('isExpandable method', function () {
            it('check if label is not expandable', function () {
                expect(expandable.isExpandable(record)).toBe(false);
            });

            it('check if label is expandable', function () {
                record['shared_catalog'].push(1);
                record['shared_catalog'].push(2);
                expect(expandable.isExpandable(record)).toBe(true);
            });

            it('check if getLabel have been called', function () {
                spyOn(expandable, 'getLabel').and.returnValues('1', '2');
                expandable.isExpandable(record);
                expect(expandable.getLabel).toHaveBeenCalled();
            });
        });

        describe('getLabelsArray method', function () {
            it('check if label array sort alphabetically case insensitive', function () {
                record['shared_catalog'].push(1, 2, 3);
                expandable.options.push({
                    label: 'Default',
                    value: '1'
                }, {
                    label: 'Label',
                    value: '2'
                }, {
                    label: 'default',
                    value: '3'
                });
                expect(expandable.getLabelsArray(record)).toEqual(['Default', 'default', 'Label']);
            });
        });
    });
});
