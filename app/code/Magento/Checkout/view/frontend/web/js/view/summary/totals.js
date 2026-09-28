/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total'
], function (Component) {
    'use strict';

    return Component.extend({
        /**
         * @return {*}
         */
        isDisplayed: function () {
            return this.isFullMode();
        }
    });
});
