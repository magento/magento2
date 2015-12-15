/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mage/translate',
    './column'
], function (_, $t, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl:'ui/grid/cells/textEdit',

            imports: {
                rows: '${ $.rowsProvider }:data.items'
            },

            listens: {
                '${ $.name }:columnData': 'setData',
                'rows': 'getData'
            },
            columnData:[],
            rows:[]
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
            debugger;
            //
        }
    });
});
