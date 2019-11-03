/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    return {
        /**
         * Validate Authorizenet-Acceptjs response
         *
         * @param {Object} context
         * @returns {jQuery.Deferred}
         */
        validate: function (context) {
            var state = $.Deferred(),
                messages = [];

            if (context.messages.resultCode === 'Ok') {
                state.resolve();
            } else {
                if (context.messages.message.length > 0) {
                    $.each(context.messages.message, function (index, element) {
                        messages.push($t(element.text));
                    });
                }
                state.reject(messages);
            }

            return state.promise();
        }
    };
});

