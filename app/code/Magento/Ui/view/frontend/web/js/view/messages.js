/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['uiComponent', '../model/messageList'], function (Component, messages) {
    'use strict';

    return Component.extend({
        errorList: messages.getAllErrors(),
        successList: messages.getAllSuccess(),

        defaults: {
            template: 'Magento_Ui/messages'
        },

        /**
         *
         * @returns {*}
         */
        isVisible: function () {
            return this.errorList().length || this.successList().length;
        },
        /**
         * Remove all errors
         */
        removeAll: function () {
            this.errorList.removeAll();
            this.successList.removeAll();
        }
    });
});
