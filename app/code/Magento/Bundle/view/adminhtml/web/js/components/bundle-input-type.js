/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry'
], function (Select, registry) {
    'use strict';

    return Select.extend({
        defaults: {
            previousType: undefined,
            parentContainer: undefined,
            selections: undefined,
            targetIndex: undefined,
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

            this._super();
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
        }
    });
});
