/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Weee/js/view/checkout/summary/weee'
], function (Component) {
    'use strict';

    return Component.extend({

        /**
         * @override
         */
        isFullMode: function () {
            return true;
        }
    });
});
