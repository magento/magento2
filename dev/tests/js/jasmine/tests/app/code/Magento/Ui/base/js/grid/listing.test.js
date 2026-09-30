/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'underscore',
    'Magento_Ui/js/grid/listing',
    'uiCollection'
], function (_, Listing, Collection) {
    'use strict';

    describe('Magento_Ui/js/grid/listing', function () {
        describe('applyPositions method', function () {
            /**
             * Builds a context running the real Collection::insertChild
             * on top of a plain array of columns.
             *
             * @param {Array} indexes
             * @returns {Object}
             */
            function createContext(indexes) {
                var elems = indexes.map(function (index) {
                    return {
                        index: index
                    };
                });

                return {
                    _elems: elems.slice(),
                    elems: elems,
                    insertChild: Collection.prototype.insertChild,
                    _insert: jasmine.createSpy('_insert'),
                    _updateCollection: jasmine.createSpy('_updateCollection')
                };
            }

            /**
             * @param {Object} context
             * @returns {Array}
             */
            function getOrder(context) {
                return _.pluck(context._elems, 'index');
            }

            it('restores the order described by saved positions', function () {
                var context = createContext(['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h']);

                Listing.prototype.applyPositions.call(context, {
                    a: 2,
                    b: 6,
                    c: 5,
                    d: 1,
                    e: 7,
                    f: 3,
                    g: 4,
                    h: 0
                });

                expect(getOrder(context)).toEqual(['h', 'd', 'a', 'f', 'g', 'c', 'b', 'e']);
            });

            it('keeps columns without saved position after positioned ones', function () {
                var context = createContext(['a', 'b', 'c', 'd', 'e']);

                Listing.prototype.applyPositions.call(context, {
                    b: 1,
                    d: 0,
                    e: 2
                });

                expect(getOrder(context)).toEqual(['d', 'b', 'e', 'a', 'c']);
            });
        });
    });
});
