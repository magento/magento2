/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'mageUtils'
], function (Element, utils) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true,
            label: '',
            error: '',
            uid: utils.uniqueid(),
            disabled: false,
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },

        /**
         * Has service
         *
         * @returns {Boolean} false.
         */
        hasService: function () {
            return false;
        },

        /**
         * Has addons
         *
         * @returns {Boolean} false.
         */
        hasAddons: function () {
            return false;
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('disabled visible value');

            return this;
        }
    });
});
