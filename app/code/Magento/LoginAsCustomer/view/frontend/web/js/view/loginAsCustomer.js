/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, Component, customerData) {
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

            this.notificationText = $.mage.__('You are connected as <strong>%1</strong> on %2')
                .replace('%1', this.customer().fullname)
                .replace('%2', this.loginAsCustomer().website_name);
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('isVisible');

            return this;
        }
    });
});
