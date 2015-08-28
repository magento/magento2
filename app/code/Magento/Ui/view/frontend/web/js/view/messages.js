/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'jquery',
        'uiComponent',
        '../model/messageList'
    ],
    function (ko, $, Component, messages) {
    'use strict';

    return Component.extend({
        errorList: messages.getAllErrors(),
        successList: messages.getAllSuccess(),

        defaults: {
            template: 'Magento_Ui/messages'
        },
        isHidden: ko.observable(false),

        initialize: function () {
            this._super();
            var self = this;
            this.isHidden.subscribe(function () {
                if (self.isHidden()) {
                    setTimeout(function () {
                        var messageSelector = '[data-role=checkout-messages]';
                        $(messageSelector).hide('blind', {}, 500)
                    }, 5000);
                }
            });
        },

        /**
         *
         * @returns {*}
         */
        isVisible: function () {
            return this.isHidden(this.errorList().length || this.successList().length);
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
