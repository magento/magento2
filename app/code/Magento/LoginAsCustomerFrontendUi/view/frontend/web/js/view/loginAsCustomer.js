/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, _, Component, customer) {
    'use strict';

    return Component.extend({

        defaults: {
            isVisible: false
        },

        /** @inheritdoc */
        initialize: function () {
            var customerData, loggedAsCustomerData;

            this._super();

            customerData = customer.get('customer');
            loggedAsCustomerData = customer.get('loggedAsCustomer');

            customerData.subscribe(function (data) {
                this.fullname = data.fullname;
                this.updateBanner();
            }.bind(this));
            loggedAsCustomerData.subscribe(function (data) {
                this.adminUserId = data.adminUserId;
                this.websiteName = data.websiteName;
                this.updateBanner();
            }.bind(this));

            this.fullname = customerData().fullname;
            this.adminUserId = loggedAsCustomerData().adminUserId;
            this.websiteName = loggedAsCustomerData().websiteName;

            this.updateBanner();
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe(['isVisible', 'notificationText']);

            return this;
        },

        /**
         * Update banner area
         *
         * @returns void
         */
        updateBanner: function () {
            if (this.adminUserId !== undefined) {
                this.isVisible(this.adminUserId);
            }

            if (this.fullname !== undefined && this.websiteName !== undefined) {
                this.notificationText($.mage.__('You are connected as <strong>%1</strong> on %2')
                    .replace('%1', _.escape(this.fullname))
                    .replace('%2', _.escape(this.websiteName)));
            }
        }
    });
});
