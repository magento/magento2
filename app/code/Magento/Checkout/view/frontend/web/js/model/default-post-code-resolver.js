/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    /**
     * Define necessity of using default post code value
     */
    var useDefaultPostCode;

    return {

        /**
         * Resolve default post code
         *
         * @returns {string|undefined}
         */
        resolve: function () {
            return useDefaultPostCode ?  window.checkoutConfig.defaultPostcode : undefined;
        },

        /**
         * Set state to useDefaultPostCode variable
         *
         * @param shouldUseDefaultPostCode
         * @returns {underscore}
         */
        setUseDefaultPostCode: function (shouldUseDefaultPostCode)
        {
            useDefaultPostCode = shouldUseDefaultPostCode;
            return this;
        }
    };
});