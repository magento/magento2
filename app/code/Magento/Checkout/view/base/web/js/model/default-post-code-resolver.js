/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
define([], function () {
    'use strict';

    /**
     * Define necessity of using default post code value
     */
    var useDefaultPostCode;

    return {
        /**
         * Resolve default post code
         *
         * @returns {String|null}
         */
        resolve: function () {
            return useDefaultPostCode ?  window.checkoutConfig.defaultPostcode : null;
        },

        /**
         * Set state to useDefaultPostCode variable
         *
         * @param {Boolean} shouldUseDefaultPostCode
         * @returns {underscore}
         */
        setUseDefaultPostCode: function (shouldUseDefaultPostCode) {
            useDefaultPostCode = shouldUseDefaultPostCode;

            return this;
        }
    };
});
