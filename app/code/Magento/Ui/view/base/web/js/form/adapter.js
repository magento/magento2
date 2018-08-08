/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/adapter/buttons'
], function ($, _, buttons) {
    'use strict';

    var selectorPrefix = '',
        eventPrefix;

    /**
     * Initialize listener.
     *
     * @param {Function} callback
     * @param {String} action
     */
    function initListener(callback, action) {
        var selector    = selectorPrefix ? selectorPrefix + ' ' + buttons[action] : buttons[action],
            elem        = $(selector)[0];

        if (!elem) {
            return;
        }

        if (elem.onclick) {
            elem.onclick = null;
        }

        $(elem).on('click' + eventPrefix, callback);
    }

    /**
     * Destroy listener.
     *
     * @param {String} action
     */
    function destroyListener(action) {
        var selector    = selectorPrefix ? selectorPrefix + ' ' + buttons[action] : buttons[action],
            elem        = $(selector)[0];

        if (!elem) {
            return;
        }

        if (elem.onclick) {
            elem.onclick = null;
        }

        $(elem).off('click' + eventPrefix);
    }

    return {

        /**
         * Attaches events handlers.
         *
         * @param {Object} handlers
         * @param {String} selectorPref
         * @param {String} eventPref
         */
        on: function (handlers, selectorPref, eventPref) {
            selectorPrefix = selectorPrefix || selectorPref;
            eventPrefix = eventPref;
            _.each(handlers, initListener);
            selectorPrefix = '';
        },

        /**
         * Removes events handlers.
         *
         * @param {Object} handlers
         * @param {String} eventPref
         */
        off: function (handlers, eventPref) {
            eventPrefix = eventPref;
            _.each(handlers, destroyListener);
        }
    };
});
