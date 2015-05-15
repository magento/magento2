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
        var filters;

        beforeEach(function () {
            filters = new Filters({
                elems: [],
                index: 'index',
                name: 'name',
                indexField: 'id',
                dataScope: 'scope',
                provider: 'provider'
            });
        });

        it('Default state - Select no fields.', function () {
            expect(filters.elems()).toEqual([]);
            filters.elems.push({id:1});
            filters.elems.push({id:1});
            expect(filters.elems()).not.toEqual([]);
        });
        it('Checks if specified filter is active.', function () {
            var filter = {id: 1};

            expect(filters.isFilterActive(filter)).toBe(false);
            filters.active().push(filter);
            expect(filters.isFilterActive(filter)).toBe(true);
        });
        it('Tells whether specified filter should be visible.', function () {
            var filter = {
                visible: function () {
                    return false;
                }
            };

            expect(filters.isFilterVisible(filter)).toBe(false);
            filters.active().push(filter);
            expect(filters.isFilterActive(filter)).toBe(true);
            filter.visible = function() {
                return true;
            };
            expect(filters.isFilterActive(filter)).toBe(true);
            filters.active().pop();
            expect(filters.isFilterActive(filter)).toBe(false);
        });
        it('Checks if collection has visible filters.', function () {
            var filter = {
                visible: function () {
                    return false;
                }
            };

            filters.elems.push(filter);
            expect(filters.hasVisible()).toBe(false);
            filter.visible = function() {
                return true;
            };
            filters.elems.push(filter);
            expect(filters.hasVisible()).toBe(true);
            filters.elems.removeAll();
            expect(filters.hasVisible()).toBe(false);
            filters.active().push(filter);
            expect(filters.hasVisible()).toBe(false);
        });
        it('Tells whether filters panel should be opened.', function () {
            var filter = {
                visible: function () {
                    return false;
                }
            };

            filters.opened(false);
            filters.elems.push(filter);
            expect(filters.isOpened()).toBe(false);
            filter.visible = function() {
                return true;
            };
            filters.elems.push(filter);
            filters.opened(true);
            expect(filters.isOpened()).toBe(true);
            filters.elems.removeAll();
            expect(filters.isOpened()).toBe(false);
            filters.active().push(filter);
            expect(filters.isOpened()).toBe(false);
        });
        it('Resets filters to the last applied state.', function () {
            filters.applied = {};
            filters.filters = {};
            filters.cancel();
            expect(filters.filters).toEqual(filters.filters);
            filters.filters = {id:1};
            filters.cancel();
            expect(filters.filters).toEqual({});
            filters.applied = {id:1};
            filters.cancel();
            expect(filters.filters).toEqual(filters.applied);
        });
        it('Sets filters data to the applied state.', function () {
            filters.applied = {};
            filters.filters = {};
            filters.apply();
            expect(filters.applied).toEqual({});
            filters.filters = {};
            filters.applied = {id:2};
            filters.apply();
            expect(filters.applied).toEqual({});
            filters.filters = {id:1};
            filters.applied = {};
            filters.apply();
            expect(filters.applied).toEqual({id:1});
            filters.filters = {id:1};
            filters.applied = {id:2};
            filters.apply();
            expect(filters.applied).toEqual({id:1});
        });
        it('Clears filters data.', function () {
            var elem = {
                value: '',
                clear: function() {
                    this.value = '';
                    return this.value;
                }
            };

            filters.active.push(elem);
            filters.applied = {};
            filters.filters = {};
            filters.clear();
            expect(filters.active.first().value).toEqual('');
            filters.active.first().value = 1;
            filters.clear();
            expect(filters.active.first().value).toEqual('');
        });
        it('Set active elements where exist value from elems.', function () {
            var elem = {
                hasData: function() {
                    return false;
                }
            };

            filters.elems.push(elem);
            filters.extractActive();
            expect(filters.active().length).toEqual(0);
            elem.hasData = function() {
                return true;
            };
            filters.elems.removeAll();
            filters.elems().push(elem);
            filters.extractActive();
            expect(filters.active().length).toEqual(1);
        });
        it('Set previews from argument elements.', function () {
            var elem = {
                getPreview: function() {
                    return true;
                }
            };

            filters.extractPreviews(filters.elems);
            expect(filters.previews().length).toEqual(0);
            filters.elems.push(elem);
            filters.extractPreviews(filters.elems);
            expect(filters.previews().length).toEqual(1);
        });
    });
});
