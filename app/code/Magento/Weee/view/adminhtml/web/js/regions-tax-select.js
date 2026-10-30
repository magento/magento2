/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    return Select.extend({
        defaults: {
            filterBy: {
                field: 'country',
                target: '${ $.parentName }.country:value'
            }
        },

        /** @inheritdoc */
        filter: function () {
            this._super();
            this.disableSelect();
        },

        /**
         * Disables select if there's no regions/states
         *
         * @returns {*} instance - Chainable
         */
        disableSelect: function () {
            var empty = !this.options().length;

            this.disabled(empty);

            if (empty) {
                this.error('');
            }

            return this;
        }
    });
});
