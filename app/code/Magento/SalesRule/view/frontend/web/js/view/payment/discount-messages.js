/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_Ui/js/view/messages',
    '../../model/payment/discount-messages'
], function (Component, messageContainer) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function (config) {
            return this._super(config, messageContainer);
        }
    });
});
