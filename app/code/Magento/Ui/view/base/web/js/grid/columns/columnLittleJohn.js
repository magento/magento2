/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'mage/translate',
    './column'
], function (ko, _, $t, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl:'ui/grid/cells/textEdit',

            imports: {
                rows: '${ $.rowsProvider }:data.items'
            },

            listens: {
                //'${ $.name }:columnData': 'setData',
                'rows': 'getData'
            }
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();


            return this;
        },

        initObservable: function(){
            this._super();
            this.observe(['rows', 'columnData']);

            this.someval = ko.pureComputed({
                /**
                 * use 'mappedValue' as value if checked
                 */
                read: function(){
                    debugger;
                    return $col.getLabel($row());
                },

                /**
                 * any value made checkbox checked
                 */
                write: function(val){
                    if (val) {
                        debugger;
                    }
                },
                owner: this});
            return this;
        },

        /**
         * Retrieve all id's from available records.
         *
         * @returns {Array} An array of {<row_id>: <cell_value>}
         */
        getData: function () {
            console.log(this.rows());
            var items = (this.rows()||[]).map(function (row) {
                    var id = row['koala_id'],
                        result = {};
                    result[id] = row[this.index]
                return result;
                }, this);

            this.set('columnData', items);
        },

        setData: function (data) {
            //
        },

        dataChanged: function (row, value) {
            this.rows()[row._rowIndex][this.index] = value;
            this.set('rows', this.rows());
        }
    });
});
