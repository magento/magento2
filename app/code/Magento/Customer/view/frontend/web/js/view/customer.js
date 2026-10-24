/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.customer = customerData.get('customer');
        }
    });
});
