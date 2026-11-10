/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (_, DynamicRows) {
    'use strict';

    /**
     * @deprecated Parent method contains labels sorting.
     * @see Magento_Ui/js/dynamic-rows/dynamic-rows
     */
    return DynamicRows.extend({
        defaults: {
            isUseDefault: '',
            serviceDisabled: false,
            listens: {
                isUseDefault: 'toggleUseDefault'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.isUseDefault(this.disabled());

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            return this._super().observe('isUseDefault serviceDisabled');
        },

        /**
         * @param {Boolean} state
         */
        toggleUseDefault: function (state) {
            if (!this.service || !this.service.template || this.serviceDisabled()) {
                return;
            }

            this.disabled(state);

            if (this.source) {
                this.source.set('data.use_default.' + this.index, Number(state));
            }
        },

        /**
         * Preserve column-level restrictions when toggling inherited rows.
         *
         * @param {Boolean} state
         */
        setDisabled: function (state) {
            this.elems.each(function (record) {
                record.elems.each(function (cell) {
                    var template = this.childTemplate &&
                        _.findWhere(this.childTemplate.children, {name: cell.index});

                    cell.disabled(state || !!(template && template.config.disabled));
                }, this);
            }, this);
        },

        /**
         * Init header elements
         *
         * @deprecated Parent method contains labels sorting.
         * @see Magento_Ui/js/dynamic-rows/dynamic-rows
         */
        initHeader: function () {
            var labels;

            this._super();
            labels = _.clone(this.labels());
            labels = _.sortBy(labels, function (label) {
                return label.sortOrder;
            });

            this.labels(labels);
        }
    });
});
