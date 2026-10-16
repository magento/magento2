/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/columns/multiselect'
], function (_, Multiselect) {
    'use strict';

    describe('ui/js/grid/columns/multiselect', function () {
        var multiSelect;

        beforeEach(function () {
            multiSelect = new Multiselect({
                rows: [],
                index: 'index',
                name: 'name',
                indexField: 'id',
                dataScope: 'scope',
                provider: 'provider'
            });
            multiSelect.source = {
                /** Stub */
                set: function () {}
            };
            spyOn(multiSelect.source, 'set');
        });

        afterEach(function () {
        });

        it('Default state - Select no rows', function () {
            multiSelect.rows([{
                id: 1
            }, {
                id: 2
            }, {
                id: 3
            }]);

            expect(multiSelect.allSelected()).toBeFalsy();
            expect(multiSelect.excluded().toString()).toEqual('');
            expect(multiSelect.selected().toString()).toEqual('');
        });

        it('Select specific several rows on several pages', function () {
            multiSelect.selected.push(4);
            multiSelect.selected.push(5);

            expect(multiSelect.allSelected()).toBeFalsy();
            expect(multiSelect.excluded().toString()).toEqual('');
            expect(multiSelect.selected().toString()).toEqual('4,5');
        });

        it('Select all rows on several pages', function () {
            multiSelect.rows([{
                id: 1
            }, {
                id: 2
            }]);
            multiSelect.selectPage();
            multiSelect.rows([{
                id: 3
            }, {
                id: 4
            }]);
            multiSelect.selectPage();

            expect(multiSelect.allSelected()).toBeFalsy();
            expect(multiSelect.excluded().toString()).toEqual('');
            expect(multiSelect.selected().toString()).toEqual('1,2,3,4');
        });

        it('Select all rows on current page with some specific rows on another page', function () {
            multiSelect.rows([{
                id: 1
            }, {
                id: 2
            }]);
            multiSelect.rows([{
                id: 3
            }, {
                id: 4
            }]);
            multiSelect.selectPage();
            multiSelect.rows([{
                id: 5
            }, {
                id: 6
            }]);
            multiSelect.selected.push(6);
            expect(multiSelect.allSelected()).toBeFalsy();
            expect(multiSelect.excluded().toString()).toEqual('5');
            expect(multiSelect.selected().toString()).toEqual('3,4,6');
        });

        it('Select all rows on several pages without some specific rows', function () {
            multiSelect.rows([{
                id: 1
            }, {
                id: 2
            }]);
            multiSelect.rows([{
                id: 3
            }, {
                id: 4
            }]);
            multiSelect.selectPage();
            multiSelect.selected.remove(4); // remove second

            expect(multiSelect.allSelected()).toBeFalsy();
            expect(multiSelect.excluded().toString()).toEqual('4');
            expect(multiSelect.selected().toString()).toEqual('3');
        });

        it('Select all rows all over the Grid', function () {
            multiSelect.rows([{
                id: 1
            }, {
                id: 2
            }]);
            multiSelect.selectAll();
            multiSelect.rows([{
                id: 3
            }, {
                id: 4
            }]);

            expect(multiSelect.allSelected()).toBeFalsy();
            expect(multiSelect.excluded().toString()).toEqual('');
            expect(multiSelect.selected().toString()).toEqual('3,4,1,2');
        });

        it('Select all rows all over the Grid and deselects all records', function () {
            multiSelect.rows([{
                id: 1
            }, {
                id: 2
            }]);

            multiSelect.selectAll();
            multiSelect.deselectAll();
            multiSelect.indetermine(2);
            expect(multiSelect.togglePage().selected()).toEqual([1, 2]);
        });

        it('Select all rows all over the Grid without all rows on current page but with specific rows on another page',
            function () {
                multiSelect.rows([{
                    id: 1
                }, {
                    id: 2
                }]);
                multiSelect.rows([{
                    id: 3
                }, {
                    id: 4
                }]);
                multiSelect.selectAll();
                multiSelect.deselectPage();
                multiSelect.rows([{
                    id: 5
                }, {
                    id: 6
                }]);

                expect(multiSelect.allSelected()).toBeFalsy();
                expect(multiSelect.excluded().toString()).toEqual('3,4');
                expect(multiSelect.selected().toString()).toEqual('5,6');
            });

        it('updateState does not call selectAll when all items are selected', function () {
            multiSelect.rows([{ id: 1 }, { id: 2 }]);
            multiSelect.totalRecords(2);
            multiSelect.excludeMode(false);
            multiSelect.selected([1, 2]);
            multiSelect.preserveSelectionsOnFilter = false;
            spyOn(multiSelect, 'selectAll').and.callThrough();
            multiSelect.updateState();

            expect(multiSelect.selectAll).not.toHaveBeenCalled();
        });

        describe('SHIFT+click range selection', function () {
            var shiftClick = {
                    shiftKey: true,
                    target: {
                        checked: true
                    }
                },
                plainClick = {
                    shiftKey: false,
                    target: {
                        checked: true
                    }
                };

            beforeEach(function () {
                multiSelect.rows([{ id: 1 }, { id: 2 }, { id: 3 }, { id: 4 }, { id: 5 }]);
            });

            it('selects the range forward', function () {
                multiSelect.onRowCheckboxClick(2, plainClick);
                multiSelect.onRowCheckboxClick(4, shiftClick);

                expect(multiSelect.selected()).toEqual([2, 3, 4]);
            });

            it('selects the range backward', function () {
                multiSelect.onRowCheckboxClick(4, plainClick);
                multiSelect.onRowCheckboxClick(2, shiftClick);

                expect(multiSelect.selected()).toEqual([2, 3, 4]);
            });

            it('deselects the range when the clicked checkbox is unchecked', function () {
                multiSelect.selected([1, 2, 3, 4, 5]);
                multiSelect.onRowCheckboxClick(2, plainClick);
                multiSelect.onRowCheckboxClick(4, {
                    shiftKey: true,
                    target: {
                        checked: false
                    }
                });

                expect(multiSelect.selected()).toEqual([1, 5]);
            });

            it('skips disabled ids', function () {
                multiSelect.disabled([3]);
                multiSelect.onRowCheckboxClick(1, plainClick);
                multiSelect.onRowCheckboxClick(5, shiftClick);

                expect(multiSelect.selected()).toEqual([1, 2, 4, 5]);
            });

            it('treats SHIFT+click without an anchor as a plain click', function () {
                multiSelect.onRowCheckboxClick(4, shiftClick);

                expect(multiSelect.selected()).toEqual([]);
                expect(multiSelect.lastSelectedId).toBe(4);
            });

            it('makes the clicked row the new anchor', function () {
                multiSelect.onRowCheckboxClick(1, plainClick);
                multiSelect.onRowCheckboxClick(3, shiftClick);
                multiSelect.onRowCheckboxClick(5, shiftClick);

                expect(multiSelect.selected()).toEqual([1, 2, 3, 4, 5]);
            });

            it('resets the anchor when rows change', function () {
                multiSelect.onRowCheckboxClick(1, plainClick);
                multiSelect.rows([{ id: 1 }, { id: 2 }, { id: 3 }]);

                expect(multiSelect.lastSelectedId).toBeNull();

                multiSelect.onRowCheckboxClick(3, shiftClick);

                expect(multiSelect.selected()).toEqual([]);
            });

            it('updates selected once for a range', function () {
                var spy = jasmine.createSpy('selectedChange');

                multiSelect.onRowCheckboxClick(1, plainClick);
                multiSelect.selected.subscribe(spy);
                multiSelect.onRowCheckboxClick(5, shiftClick);

                expect(spy.calls.count()).toBe(1);
            });

            it('puts deselected range ids into excluded in exclude mode', function () {
                multiSelect.selectAll();
                multiSelect.onRowCheckboxClick(2, plainClick);
                multiSelect.onRowCheckboxClick(4, {
                    shiftKey: true,
                    target: {
                        checked: false
                    }
                });

                expect(multiSelect.excluded()).toEqual([2, 3, 4]);
                expect(multiSelect.selected()).toEqual([1, 5]);
            });

            it('returns true to keep the default checkbox action', function () {
                expect(multiSelect.onRowCheckboxClick(1, plainClick)).toBe(true);
                expect(multiSelect.onRowCheckboxClick(3, shiftClick)).toBe(true);
            });
        });
    });
});
