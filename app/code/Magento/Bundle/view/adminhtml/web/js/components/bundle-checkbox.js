/**
 * Copyright © Magento, Inc. All rights reserved.
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
            parentContainer: '',
            parentSelections: '',
            changer: ''
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
            this.imports.changeType = this.retrieveParentName(this.parentContainer) + '.' + this.changer + ':value';

            return this;
        },

        /**
         * @inheritdoc
         */
        onUpdate: function () {
            if (this.prefer === 'radio' && this.checked() && !this.clearing) {
                this.clearValues();
            }

            this._super();
        },

        /**
         * Checkbox to radio type changer.
         *
         * @param {String} type - type to change.
         */
        changeType: function (type) {
            var typeMap = registry.get(this.retrieveParentName(this.parentContainer) + '.' + this.changer).typeMap;

            this.prefer = typeMap[type];
            this.elementTmpl(this.templates[typeMap[type]]);
        },

        /**
         * Clears values in components like this.
         */
        clearValues: function () {
            var records = registry.get(this.retrieveParentName(this.parentSelections)),
                index = this.index,
                uid = this.uid;

            records.elems.each(function (record) {
                record.elems.filter(function (comp) {
                    return comp.index === index && comp.uid !== uid;
                }).each(function (comp) {
                    comp.clearing = true;
                    comp.clear();
                    comp.clearing = false;
                });
            });
        },

        /**
         * Retrieve name for the most global parent with provided index.
         *
         * @param {String} parent - parent name.
         * @returns {String}
         */
        retrieveParentName: function (parent) {
            return this.name.replace(new RegExp('^(.+?\\.)?' + parent + '\\..+'), '$1' + parent);
        }
    });
});
