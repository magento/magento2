/**
 * Copyright 2015 Adobe
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
                'postcode': {
                    'required': true
                },
                'country_id': {
                    'required': true
                },
                'region_id': {
                    'required': true
                },
                'region_id_input': {
                    'required': true
                }
            };
        }
    };
});
