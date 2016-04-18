/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, totalsService) {
        'use strict';

        return Component.extend({

            isLoading: totalsService.isLoading
        });
    }
);
