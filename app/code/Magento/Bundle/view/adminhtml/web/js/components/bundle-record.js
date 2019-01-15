/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/dynamic-rows/record',
    'uiRegistry'
], function (Record, registry) {
    'use strict';

    return Record.extend({
        /**
         * @param {String} val - type of Input Type
         */
        onTypeChanged: function (val) {
            var columnVisibility  = !(val === 'multi' || val === 'checkbox');

            registry.async(this.name + '.' + 'selection_can_change_qty')(function (elem) {
                elem.visible(columnVisibility);
            });
        }
    });
});
