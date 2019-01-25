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
         * @param {Object} context
         * @returns {Object}
         */
        validate: function (context) {
            var state = $.Deferred(),
                i = 0;

            if (context.messages.resultCode === 'Ok') {
                state.resolve();
            } else {
                for (; i < context.messages.message.length; i++) {
                    state.reject($t(context.messages.message[i].text));
                }
            }

            return state.promise();
        }
    };
});

