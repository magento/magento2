/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        '../shipping-rates-validation-rules/freeshipping'
    ],
    function ($, validationRules) {
        "use strict";
        return {
            validate: function(address) {
                return address.countryId != undefined;
            }
        };
    }
);
