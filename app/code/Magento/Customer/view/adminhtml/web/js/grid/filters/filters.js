/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Ui/js/grid/filters/filters'
], function (Filters) {
    'use strict';

    return Filters.extend({
        defaults: {
            chipsConfig: {
                name: '${ $.name }_chips',
                provider: '${ $.chipsConfig.name }',
                component: 'Magento_Customer/js/grid/filters/chips'
            }
        }
    });
});
