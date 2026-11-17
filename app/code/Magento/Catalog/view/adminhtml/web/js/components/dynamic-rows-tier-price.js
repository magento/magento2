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
            uid: '${ $.name }',
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
                    this.setCellDisabled(cell, state, this.childTemplate && this.childTemplate.children);
                }, this);
            }, this);
        },

        /**
         * Apply inheritance to fields inside groups without removing configured locks.
         *
         * @param {Object} cell
         * @param {Boolean} state
         * @param {Array} templates
         */
        setCellDisabled: function (cell, state, templates) {
            var template = _.findWhere(templates || [], {name: cell.index}),
                disabled = state || !!(template && template.config && template.config.disabled);

            if (_.isFunction(cell.disabled)) {
                cell.disabled(disabled);
            }

            if (cell.elems) {
                cell.elems.each(function (child) {
                    this.setCellDisabled(child, disabled, template && template.children);
                }, this);
            }
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
