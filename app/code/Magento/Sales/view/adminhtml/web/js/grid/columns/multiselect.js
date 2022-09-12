/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/columns/multiselect'
], function (Multiselect) {
    'use strict';

    return Multiselect.extend({
        /**
         * Prevent calling this.selectAll(); in parent
         *
         * @returns  {Object}
         */
        updateState: function () {
            var selected = this.selected().length,
                totalRecords = this.totalRecords();

            if (totalRecords && selected === totalRecords && !this.preserveSelectionsOnFilter) {
                return this;
            }
            return this._super();
        }
    });
});
