define(function () {
    'use strict';

    var MESSAGES = {};

    return {
        /**
         * Returns message that matches the provided code.
         *
         * @param {String} code - Message's identifier
         * @returns {String}
         */
        getMessage: function (code) {
            return MESSAGES[code];
        },

        /**
         * Adds a new message to the poll.
         *
         * @param {String} code - Message's identifier.
         * @param {String} message - Text of the message
         */
        addMessage: function (code, message) {
            MESSAGES[code] = message;
        },

        /**
         * Tells whether message with provide code exists in the poll.
         *
         * @param {String} code - Message's identifier.
         * @returns {Boolean}
         */
        hasMessage: function (code) {
            return MESSAGES.hasOwnProperty(code);
        }
    };
});
