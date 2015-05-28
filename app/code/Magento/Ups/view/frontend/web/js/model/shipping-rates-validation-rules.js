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
                //TODO: example rules
                return {
                    'postcode': {
                        'required': true,
                        'min-length': 3
                    },
                    'street': {
                        'required': true
                    }
                };
            }
        };
    }
);
