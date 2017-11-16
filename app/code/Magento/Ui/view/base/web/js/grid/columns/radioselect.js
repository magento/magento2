/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mage/translate',
    './column'
], function (_, $t, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/radioselect',
            draggable: false,
            sortable: false,
            selectedVariableCode: null
        },
        initObservable: function () {
            this._super().observe(['selectedVariableCode']);

            return this;
        }
    });
});
