/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'Magento_Catalog/js/product/list/column-status-validator'
], function (Column, columnStatusValidator) {
    'use strict';

    return Column.extend({
        /**
         * Depends on this option, "Learn More" link can be shown or hide. Depends on  backend configuration
         *
         * @returns {Boolean}
         */
        isAllowed: function () {
            return columnStatusValidator.isValid(this.source(), 'learn_more', 'show_attributes');
        }
    });
});
