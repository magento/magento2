/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

define([], function () {
    'use strict';

    return {
        /**
         * @return {Object}
         */
        getRules: function () {
            return {
                'country_id': {
                    'required': true
                }
            };
        }
    };
});
