/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
    'underscore',
    'mageUtils'
], function (DynamicRows, _, utils) {
    'use strict';

    var maxId = 0;

    return DynamicRows.extend({
        defaults: {
            mappingSettings: {
                enabled: false,
                distinct: false
            },
            update: true,
            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            map: {
                option_id: 'option_id'
            },
            // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
            identificationProperty: 'option_id',
            identificationDRProperty: 'option_id'
        },

        /** @inheritdoc */
        initialize: function () {
            this._super().initMaxId();

            return this;
        },

        /** @inheritdoc */
        processingInsertData: function (data) {
            var options = [],
                currentOption;

            if (!data) {
                return;
            }
            data.each(function (item) {
                if (!item.options) {
                    return;
                }
                item.options.each(function (option) {
                    currentOption = utils.copy(option);

                    if (currentOption.hasOwnProperty('sort_order')) {
                        delete currentOption['sort_order'];
                    }
                    currentOption['option_id'] = ++maxId;
                    options.push(currentOption);
                });
            });

            if (!options || !options.length) {
                return;
            }
            this.cacheGridData = options;
            options.each(function (opt) {
                this.mappingValue(opt);
            }, this);

            this.insertData([]);
        },

        /**
         * Set empty array to dataProvider
         */
        clearDataProvider: function () {
            this.source.set(this.dataProvider, []);
        },

        /** @inheritdoc */
        processingAddChild: function (ctx, index, prop) {
            if (ctx && !_.isNumber(ctx['option_id'])) {
                ctx['option_id'] = ++maxId;
            }
            this._super(ctx, index, prop);
        },

        /**
         * Stores max option_id value of the options from recordData once on initialization
         *
         */
        initMaxId: function () {
            var data = this.recordData();

            maxId = data && data.length ? ~~_.max(data, function (record) {
                return ~~record['option_id'];
            })['option_id'] : 0;
        }
    });
});
