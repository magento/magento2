/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'mageUtils'
], function (utils) {
    'use strict';

    return {
        validatedPostCodeExample: [],

        /**
         * @param {*} postCode
         * @param {*} countryId
         * @param {Array} postCodesPatterns
         * @return {Boolean}
         */
        validate: function (postCode, countryId, postCodesPatterns) {
            var pattern, regex,
                patterns = postCodesPatterns ? postCodesPatterns[countryId] :
                    window.checkoutConfig.postCodes[countryId];

            this.validatedPostCodeExample = [];

            if (!utils.isEmpty(postCode) && !utils.isEmpty(patterns)) {
                for (pattern in patterns) {
                    if (patterns.hasOwnProperty(pattern)) { //eslint-disable-line max-depth
                        this.validatedPostCodeExample.push(patterns[pattern].example);
                        regex = new RegExp(patterns[pattern].pattern);

                        if (regex.test(postCode)) { //eslint-disable-line max-depth
                            return true;
                        }
                    }
                }

                return false;
            }

            return true;
        }
    };
});
