/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    return Select.extend({
        defaults: {
            imports: {
                filter: '${ $.parentName }.country:value',
                disableSelect: '${ $.parentName }.country:value'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            return this._super()
                .disableSelect();
        },

        /**
         * Disables select if there's no regions/states
         *
         * @returns {*} instance - Chainable
         */
        disableSelect: function () {
            var empty = !(this.options().length - 1);

            this.disabled(empty);

            if (empty) {
                this.error('');
            }

            return this;
        }
    });
});
