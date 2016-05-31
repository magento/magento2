/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();

            this.template = window.checkout.imageTemplate || this.template;
        }
    });
});
