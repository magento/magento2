/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['mageUtils'], function (utils) {
    'use strict';
    return {
        validatedPostCodeExample: [],
        validate: function(postCode, countryId) {
            var patterns = window.checkoutConfig.postCodes[countryId];
            this.validatedPostCodeExample = [];

            if (!utils.isEmpty(postCode) && !utils.isEmpty(patterns)) {
                for (var pattern in patterns) {
                    if (patterns.hasOwnProperty(pattern)) {
                        this.validatedPostCodeExample.push(patterns[pattern]['example']);
                        var regex = new RegExp(patterns[pattern]['pattern']);
                        if (regex.test(postCode)) {
                            return true;
                        }
                    }
                }
                return false;
            }
            return true;
        }
    }
});
