/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(function () {
    'use strict';

    var MESSAGES = {
        templateStartLoading:
            'The "${ $.template }" template requested by  the "${$.component}" component started loading.',
        templateLoadedFromServer:
            'The "${ $.template }" template requested by the "${$.component}" component  was loaded from server."',
        templateLoadedFromCache:
            'The "${ $.template }" template  requested by the "${$.component}" component was loaded from cache."',
        templateLoadingFail: 'Failed to load the "${ $.template }" template requested by "${$.component}".',
        componentStartInitialization:
            'Component "${$.component}" start initialization with instance name "${$.componentName}".',
        componentStartLoading: ' Started loading the "${$.component}" component.',
        componentFinishLoading: 'The "${$.component}" component was loaded.',
        componentLoadingFail: 'Failed to load the "${$.component}" component.',
        depsLoadingFail: 'Could not get the declared "${$.deps}" dependency for the "${$.component}" instance.',
        depsStartRequesting: 'Requesting the "${$.deps}" dependency for the "${$.component}" instance.',
        depsFinishRequesting: 'The "${$.deps}" dependency for the "${$.component}" instance was received.',
        requestingComponent: 'Requesting the "${$.component}" component.',
        requestingComponentIsLoaded: 'The requested "${$.component}" component was received.',
        requestingComponentIsFailed: 'Could not get the requested "${$.component}" component.'
    };

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
