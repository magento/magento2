/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    './select'
], function (_, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            customerId: null,
            isGlobalScope: 0
        },

        initialize: function () {
            this._super();
           
            if (this.isGlobalScope) {
                this.setVisible(false);
            }

            if (this.customerId) { //disable element if customer exists
                this.disable(true);
            }

            return this;
        }
    });
});

