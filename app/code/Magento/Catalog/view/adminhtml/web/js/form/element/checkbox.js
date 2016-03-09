/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox',
    'uiRegistry'
], function (Checkbox, rg) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            inputName: '',
            value: '',
            prefixElementName: '',
            parentDynamicRowName: 'text_swatch'
        },

        /**
         * Parses options and merges the result with instance
         *
         * @param  {Object} config
         * @returns {Object} Chainable.
         */
        initConfig: function (config) {
            var recordId;

            this._super();

            this.inputName = config.inputName;
            recordId = rg.get(this.parentName).recordId;
            this.value = this.prefixElementName + recordId;

            return this;
        },

        /**
         * @inheritdoc
         */
        onUpdate: function () {
            if (this.prefer === 'radio' && !this.clearing) {
                this.clearValues();
            } else if (this.prefer === 'radio') {
                this.clearing = false;
            }

            this._super();
        },

        /**
         * Clears values in components like this.
         */
        clearValues: function () {
            var records = rg.get(this.resolveParentName(this.parentDynamicRowName)),
                index = this.index,
                uid = this.uid;

            this.clearing = true;
            records.elems.each(function (record) {
                record.elems.filter(function (comp) {
                    return comp.index === index && comp.uid !== uid;
                }).each(function (comp) {
                    comp.clear();
                });
            });
        },

        resolveParentName: function (parent) {
            return this.name.split("." + parent + ".")[0] + "." + parent;
        }
    });
});
