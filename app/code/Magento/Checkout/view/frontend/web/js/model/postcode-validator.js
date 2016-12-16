/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
         * @return {Boolean}
         */
        validate: function (postCode, countryId) {
            var patterns = window.checkoutConfig.postCodes[countryId],
                pattern, regex;

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
