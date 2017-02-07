/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
