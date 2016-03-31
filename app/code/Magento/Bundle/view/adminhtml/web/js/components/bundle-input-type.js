/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry'
], function (Select, registry) {
    'use strict';

    return Select.extend({
        defaults: {
            previousType: '',
            parentContainer: '',
            selections: '',
            targetIndex: '',
            typeMap: {}
        },

        /**
         * @inheritdoc
         */
        onUpdate: function () {
            var type = this.typeMap[this.value()];

            if (type !== this.previousType) {
                this.previousType = type;

                if (type === 'radio') {
                    this.clearValues();
                }
            }

            this.toggleCheckbox(type === 'radio');

            this._super();
        },

        /**
         * Toggle 'User Defined' checkbox in selections
         * @param {Boolean} visibility
         */
        toggleCheckbox: function(visibility) {
            var records = registry.get(this.retrieveParentName(this.parentContainer) + '.' + this.selections);

            records.elems.each(function (record) {
                record.elems.filter(function (comp) {
                    return comp.index === this.targetCheckbox;
                }, this).each(function (comp) {
                    comp.visible(visibility);
                });
            }, this);
        },

        /**
         * Clears values in components like this.
         */
        clearValues: function () {
            var records = registry.get(this.retrieveParentName(this.parentContainer) + '.' + this.selections),
                checkedFound = false;

            records.elems.each(function (record) {
                record.elems.filter(function (comp) {
                    return comp.index === this.targetIndex;
                }, this).each(function (comp) {
                    if (comp.checked()) {
                        if (checkedFound) {
                            comp.clearing = true;
                            comp.clear();
                            comp.clearing = false;
                        }

                        checkedFound = true;
                    }
                });
            }, this);
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
