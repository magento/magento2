/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'underscore'
    ], function (_) {
        'use strict';

        return {

            /**
             * Build query to get id
             *
             * @param {Object} data
             */
            buildQuery: function (data) {
                var filters = [];

                _.each(data, function (value, key) {
                    filters.push({
                        field: key,
                        value: value,
                        'condition_type': 'in'
                    });
                });

                return {
                    searchCriteria: {
                        filterGroups: [
                            {
                                filters: filters
                            }
                        ]
                    }
                };
            }
        };
    }
);
