/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
/*jscs:disable jsDoc*/
define(['Magento_Customer/js/form/components/insert-listing'], function (Constr) {
    'use strict';

    describe('Magento_Customer/js/form/components/insert-listing', function () {
        var obj,
            ids = ['1', '2'],
            data = {
                action: 'delete',
                data: {
                    selected: ids
                }
            },
            selectionsProvider = {
                selected: jasmine.createSpy().and.returnValue(ids),
                deselect: jasmine.createSpy()
            };

        beforeEach(function () {
            obj = new Constr({
                name: 'content_name',
                selections: function () {
                    return selectionsProvider;
                }
            });
        });

        describe('Check delete massaction process', function () {
            it('Check call to deleteMassaction method', function () {
                obj.deleteMassaction = {
                    call: jasmine.createSpy()
                };
                obj.onMassAction(data);

                expect(obj.deleteMassaction.call).toHaveBeenCalledWith(obj, {
                    selected: ids
                });
            });

            it('Check ids are retrieved from selections provider if they are NOT in data', function () {
                obj._delete = jasmine.createSpy();
                obj.onMassAction({
                    action: 'delete',
                    data: {}
                });

                expect(selectionsProvider.selected).toHaveBeenCalled();
                selectionsProvider.selected.calls.reset();
                expect(obj._delete).toHaveBeenCalledWith([1, 2]);
            });

            it('Check removal of default addresses and selections by provided ids', function () {
                obj.source = {
                    get: jasmine.createSpy().and.returnValues(2, 3),
                    set: jasmine.createSpy()
                };
                obj.onMassAction(data);

                expect(selectionsProvider.selected).not.toHaveBeenCalled();
                expect(obj.source.get.calls.count()).toEqual(2);
                expect(obj.source.set.calls.count()).toEqual(1);
                expect(selectionsProvider.deselect.calls.count()).toEqual(2);
            });
        });
    });
});
