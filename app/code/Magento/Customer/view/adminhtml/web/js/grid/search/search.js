/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/search/search'
], function (Search) {
    'use strict';

    return Search.extend({
        defaults: {
            statefull: {
                value: false
            }
        }
    });
});
