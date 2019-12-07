/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore'
], function (_) {
    'use strict';

    return _.extend({
        /**
         * Check whether we can show column depends on server settings or not
         *
         * @param {Object} source
         * @param {String} attributeCode
         * @param {String} type
         * @returns {Boolean}
         */
        isValid: function (source, attributeCode, type) {
            var attributes;

            if (!source[type]) {
                return false;
            }

            attributes = source[type].split(',');

            return _.contains(attributes, attributeCode);
        }
    });
});
