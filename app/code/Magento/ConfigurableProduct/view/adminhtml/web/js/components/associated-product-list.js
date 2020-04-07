/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'ko'
], function (Component, ko) {
    "use strict";
    return Component.extend({
        defaults: {
            isShowAddProductButton: ko.observable(false)
        }
    });
});