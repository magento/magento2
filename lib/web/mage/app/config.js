/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    return {
        /**
         * Get base url.
         */
        getBaseUrl: function () {
            return this.values.baseUrl;
        },

        /**
         * Get form key.
         */
        getFormKey: function () {
            return this.values.formKey;
        }
    };
});
