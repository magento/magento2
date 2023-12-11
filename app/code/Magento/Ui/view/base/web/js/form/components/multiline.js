/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    './group'
], function (Group) {
    'use strict';

    return Group.extend({
        defaults: {
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },

        /**
         * Initialize Multiline component.
         *
         * @returns {Object}
         */
        initialize: function () {
            return this._super()
                ._prepareValue();
        },

        /**
         * {@inheritdoc}
         */
        initObservable: function () {
            this._super()
                .observe('value');

            return this;
        },

        /**
         * Prepare value for Multiline options.
         *
         * @returns {Object} Chainable.
         * @private
         */
        _prepareValue: function () {
            var value = this.value();

            if (typeof value === 'string') {
                this.value(value.split('\n'));
            }

            return this;
        }
    });
});
