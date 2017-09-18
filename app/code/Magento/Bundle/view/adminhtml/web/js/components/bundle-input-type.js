/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @deprecated Not used anymore
 * @see Magento_Bundle/js/components/bundle-record
 * @see Magento_Bundle/js/components/bundle-checkbox
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
                this.processSelections(type === 'radio');
            }

            this._super();
        },

        /**
         * Toggle 'User Defined' column and clears values
         * @param {Boolean} isRadio
         */
        processSelections: function (isRadio) {
            var records = registry.get(this.retrieveParentName(this.parentContainer) + '.' + this.selections),
                checkedFound = false;

            records.elems.each(function (record) {
                record.elems.filter(function (comp) {
                    return comp.index === this.userDefinedIndex;
                }, this).each(function (comp) {
                    comp.visible(isRadio);
                });

                if (isRadio) {
                    record.elems.filter(function (comp) {
                        return comp.index === this.isDefaultIndex;
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
                }
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
