/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
