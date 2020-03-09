/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({

        defaults: {
            isVisible: false
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.customer = customerData.get('customer');
            this.loginAsCustomer = customerData.get('logged_as_customer');
            this.isVisible(this.loginAsCustomer().admin_user_id);
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('isVisible');

            return this;
        }
    });
});
