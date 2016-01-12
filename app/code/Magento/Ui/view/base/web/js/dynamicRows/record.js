/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'uiCollection'
], function (_, registry, uiCollection) {
    'use strict';

    return uiCollection.extend({
        defaults: {
            parentComponent: null,
            sortNamespace: 'sortOrder',
            sorting: true,
            curSortOrder: null,
            curSortOrderProperty: 'curSortOrder',
            defaultHeaderLabel: '',
            stack: [],
            label: '',
            exports: {
                sortNamespace: '${ $.parent }:sortNamespace',
                curSortOrderProperty: '${ $.parent }:curSortOrderProperty'
            },
            listens: {
                '${ $.provider }:${ $.dataScope }.${ $.sortNamespace }': 'checkSortOrder'
            },
            modules: {
                parentComponent: '${ $.parentName }'
            }
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('label');

            return this;
        },

        /**
         * @param {String} label
         * @returns {String}
         */
        getHeaderLabel: function (label) {
            if (_.isString(label)) {
                this.label(label);
            } else if (label && this.label()) {
                return this.label();
            } else {
                this.label(this.defaultHeaderLabel);
            }

            return this.label();
        },

        /**
         * Check sort order and call sorted method
         * @param {Object} data - record instance
         * @returns {Boolean}
         */
        checkSortOrder: function (data) {
            var path,
                curSortOrder,
                sortPositionDeps;

            if (!this.sorting || this.parentComponent().collision) {
                return false;
            }

            if (data) {
                sortPositionDeps = this.hasDeps(data);

                if (sortPositionDeps) {
                    this.updateSortingPosition(sortPositionDeps);
                }

                this.sort(data);
            } else {
                path = this.dataScope + '.' + this.sortNamespace;
                curSortOrder = this.parentComponent().getMaxSortOrder() + 1;
                this.source.set(path, curSortOrder);
            }

            this.curSortOrder = data || curSortOrder;
        },

        /**
         * Check elements with same sort order
         * @param {Object} data - record instance
         * @returns {Array} deps elements.
         */
        hasDeps: function (data) {
            var elems = this.parentComponent().elems(),
                result = false;

            elems.each(function (elem) {
                if (parseInt(elem[this.curSortOrderProperty], 10) === parseInt(data, 10)) {
                    result = elem;
                }
            }, this);

            return result;
        },

        /**
         * Update element sort order property
         * @param {Object} elem - element instance
         */
        updateSortingPosition: function (elem) {
            var path,
                dep,
                val;

            this.stack.push(elem);
            val = parseInt(elem[this.curSortOrderProperty], 10);
            val++;
            dep = this.hasDeps(val);

            if (dep) {
                this.updateSortingPosition(dep);
            } else {
                this.parentComponent().collision = true;
                this.stack.each(function (ins) {
                    path = ins.dataScope + '.' + this.sortNamespace;
                    ins[this.curSortOrderProperty] = parseInt(ins[this.curSortOrderProperty], 10);
                    ins[this.curSortOrderProperty]++;
                    this.source.set(path, ins[this.curSortOrderProperty]);
                }, this);
                this.stack = [];
                this.parentComponent().collision = false;
            }
        },

        /**
         * Sorted records by sorting position
         * @param {String|Number} index - current record index
         */
        sort: function (index) {
            var componentElems = this.parentComponent().elems(),
                indexCollection,
                array = [],
                withOutParam = {},
                withOutSortOrder;

            this.curSortOrder = index;

            withOutParam[this.curSortOrderProperty] = null;
            withOutSortOrder = _.where(componentElems, withOutParam);

            indexCollection = _.indexBy(componentElems, this.curSortOrderProperty);
            indexCollection.hasOwnProperty('null') ? delete indexCollection.null : false;

            _.each(indexCollection, function (elem) {
                array.push(elem);
            }, this);

            array = _.union(array, withOutSortOrder);

            this.parentComponent().elems(array);
        }
    });
});
