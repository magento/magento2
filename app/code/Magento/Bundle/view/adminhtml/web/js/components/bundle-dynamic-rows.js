/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (_, utils, registry, dynamicRows) {
    'use strict';

    return dynamicRows.extend({
        defaults: {
            label: '',
            collapsibleHeader: true,
            columnsHeader: false,
            deleteProperty: false,
            addButton: false
        },

        /**
         * Set new data to dataSource,
         * delete element
         *
         * @param {Array} data - record data
         */
        _updateData: function (data) {
            var elems = _.clone(this.elems()),
                path,
                dataArr,
                optionBaseData;

            dataArr = this.recordData.splice(this.startIndex, this.recordData().length - this.startIndex);
            dataArr.splice(0, this.pageSize);
            elems = _.sortBy(this.elems(), function (elem) {
                return ~~elem.index;
            });

            data.concat(dataArr).forEach(function (rec, idx) {
                if (elems[idx]) {
                    elems[idx].recordId = rec[this.identificationProperty];
                }

                if (!rec.position) {
                    rec.position = this.maxPosition;
                    this.setMaxPosition();
                }

                path = this.dataScope + '.' + this.index + '.' + (this.startIndex + idx);
                optionBaseData = _.pick(rec, function (value) {
                    return !_.isObject(value);
                });
                this.source.set(path, optionBaseData);
                this.source.set(path + '.bundle_button_proxy', []);
                this.source.set(path + '.bundle_selections', []);
                this.removeBundleItemsFromOption(idx);
                _.each(rec['bundle_selections'], function (obj, index) {
                    this.source.set(path + '.bundle_button_proxy' + '.' + index, rec['bundle_button_proxy'][index]);
                    this.source.set(path + '.bundle_selections' + '.' + index, obj);
                }, this);
            }, this);

            this.elems(elems);
        },

        /**
         *  Removes nested dynamic-rows-grid rendered records from option
         *
         * @param {Number|String} index - element index
         */
        removeBundleItemsFromOption: function (index) {
            var bundleSelections = registry.get(this.name + '.' + index + '.' + this.bundleSelectionsName),
                bundleSelectionsLength = (bundleSelections.elems() || []).length,
                i;

            if (bundleSelectionsLength) {
                for (i = 0; i < bundleSelectionsLength; i++) {
                    bundleSelections.elems()[0].destroy();
                }
            }
        },

        /**
        * {@inheritdoc}
        */
        processingAddChild: function (ctx, index, prop) {
            var recordIds = _.map(this.recordData(), function (rec) {
                return parseInt(rec['record_id'], 10);
            }),
            maxRecordId = _.max(recordIds);

            prop = maxRecordId > -1 ? maxRecordId + 1 : prop;
            this._super(ctx, index, prop);
        }
    });
});
