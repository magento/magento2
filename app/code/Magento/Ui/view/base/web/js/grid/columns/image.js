/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/columns/image',
            modules: {
                previewComponent: '${ $.parentName }.preview'
            },
            previewRowId: null,
            previewHeight: 0,
            fields: {
                id: 'id',
                url: 'url'
            }
        },

        /**
         * Init observable variables
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe([
                    'previewRowId',
                    'previewHeight'
                ]);

            return this;
        },

        /**
         * Returns url to given record.
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {String}
         */
        getUrl: function (record) {
            return record[this.fields.url];
        },

        /**
         * Returns id to given record.
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {Number}
         */
        getId: function (record) {
            return record[this.fields.id];
        },

        /**
         * Returns container styles to given record.
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {Object}
         */
        getStyles: function (record) {
            var styles = record.styles();

            styles['margin-bottom'] = this.previewRowId() === record.rowNumber ? this.previewHeight : 0;
            record.styles(styles);

            return record.styles;
        },

        /**
         * Returns class list to given record.
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {Object}
         */
        getClasses: function (record) {
            return record.css || {};
        },

        /**
         * Expand image preview
         */
        expandPreview: function (record) {
            this.previewComponent().show(record);
        }
    });
});
