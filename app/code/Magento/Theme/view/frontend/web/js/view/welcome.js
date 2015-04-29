/**
* Copyright © 2015 Magento. All rights reserved.
* See COPYING.txt for license details.
*/
define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery'
], function (Component, customerData, $) {
    'use strict';

    return Component.extend({
        welcome: function() {
            return $.mage.__('Welcome, ') + customerData.get('customer')().fullname + '!';
        }
    });
});
