/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/paging/sizes'
], function (Sizes) {
    'use strict';

    return Sizes.extend({
        defaults: {
            options: {
                '20': {
                    value: 20,
                    label: 20
                },
                '30': {
                    value: 30,
                    label: 30
                },
                '50': {
                    value: 50,
                    label: 50
                }
            },
            value: 20
        }
    });
});
