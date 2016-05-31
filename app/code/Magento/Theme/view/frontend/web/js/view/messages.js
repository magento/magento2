/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery/jquery-storageapi'
], function ($, Component, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            cookieMessages: [],
            messages: []
        },
        initialize: function () {
            this._super();

            this.cookieMessages = $.cookieStorage.get('mage-messages');
            this.messages = customerData.get('messages').extend({disposableCustomerData: 'messages'});
            $.cookieStorage.setConf({path: '/', expires: -1}).set('mage-messages', null);
        }
    });
});
