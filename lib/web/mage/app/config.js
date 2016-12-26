/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable strict */
define([], function () {
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
