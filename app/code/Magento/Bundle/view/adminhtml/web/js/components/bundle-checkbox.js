/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox',
    'uiRegistry'
], function (Checkbox, registry) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            clearing: false,
            parentContainer: 'product_bundle_container',
            parentSelections: 'bundle_selections',
            changer: 'option_info.type'
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super().
                observe('elementTmpl');

            return this;
        },

        /**
         * @inheritdoc
         */
        initConfig: function () {
            this._super();
            this.imports.changeType = this.getParentName(this.parentContainer) + '.' + this.changer + ':value';

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
         * Getter for parent name. Split string by provided parent name.
         *
         * @param {String} parent - parent name.
         * @returns {String}
         */
        getParentName: function (parent) {
            return this.name.split(parent)[0] + parent;
        },

        /**
         * Checkbox to radio type changer.
         *
         * @param {String} type - type to change.
         */
        changeType: function (type) {
            if (type === 'select') {
                type = 'radio';
            } else if (type === 'multi') {
                type = 'checkbox';
            }

            this.prefer = type;
            this.clear();
            this.elementTmpl(this.templates[type]);
            this.clearing = false;
        },

        /**
         * Clears values in components like this.
         */
        clearValues: function () {
            var records = registry.get(this.getParentName(this.parentSelections)),
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
        }
    });
});
