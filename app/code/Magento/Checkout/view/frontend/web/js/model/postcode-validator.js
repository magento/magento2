/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['mageUtils'], function (utils) {
    return function(postCode, countryId) {
        "use strict";
        var patterns = window.checkoutConfig.postCodes[countryId];

        if (!utils.isEmpty(postCode) && !utils.isEmpty(patterns)) {
            for (var pattern in patterns) {
                if (patterns.hasOwnProperty(pattern)) {
                    var regex = new RegExp(patterns[pattern]);
                    if (regex.test(postCode)) {
                        return true;
                    }
                }
            }
            return false;
        }
        return true;
    }
});
