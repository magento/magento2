/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [],
    function () {
        "use strict";
        return {
            getRules: function() {
                return {
                    'country_id': {
                        'required': true
                    },
                    'postcode': {
                        'required': false
                    }
                };
            }
        };
    }
);
