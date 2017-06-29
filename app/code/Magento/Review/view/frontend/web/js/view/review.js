/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Magento_Customer/js/view/customer'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.review = customerData.get('review').extend({
                disposableCustomerData: 'review'
            });
        },

        /**
         * @return {*}
         */
        nickname: function () {
            return this.review().nickname || customerData.get('customer')().firstname;
        }
    });
});
