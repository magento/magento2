/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global FORM_KEY */
define([
    'jquery',
    'mage/apply/main',
    'mage/backend/notification',
    'Magento_Ui/js/lib/knockout/bootstrap',
    'mage/mage',
    'mage/translate'
], function ($, mage, notification) {
    'use strict';

    var bootstrap;

    $.ajaxSetup({
        /*
         * @type {string}
         */
        type: 'POST',

        /**
         * Ajax before send callback.
         *
         * @param {Object} jqXHR - The jQuery XMLHttpRequest object returned by $.ajax()
         * @param {Object} settings
         */
        beforeSend: function (jqXHR, settings) {
            var formKey = typeof FORM_KEY !== 'undefined' ? FORM_KEY : null;

            if (!settings.url.match(new RegExp('[?&]isAjax=true',''))) {
                settings.url = settings.url.match(
                    new RegExp('\\?', 'g')) ?
                    settings.url + '&isAjax=true' :
                    settings.url + '?isAjax=true';
            }

            if (!settings.data) {
                settings.data = {
                    'form_key': formKey
                };
            } else if (typeof settings.data === 'string' &&
                settings.data.indexOf('form_key=') === -1) {
                settings.data += '&' + $.param({
                    'form_key': formKey
                });
            } else if ($.isPlainObject(settings.data) && !settings.data['form_key']) {
                settings.data['form_key'] = formKey;
            }
        },

        /**
         * Ajax complete callback.
         *
         * @param {Object} jqXHR - The jQuery XMLHttpRequest object returned by $.ajax()
         */
        complete: function (jqXHR) {
            var jsonObject;

            if (jqXHR.readyState === 4) {
                try {
                    jsonObject = JSON.parse(jqXHR.responseText);

                    if (jsonObject.ajaxExpired && jsonObject.ajaxRedirect) { //eslint-disable-line max-depth
                        window.location.replace(jsonObject.ajaxRedirect);
                    }
                } catch (e) {}
            }
        },

        /**
         * Error callback.
         */
        error: function () {
            $('body').notification('clear')
                .notification('add', {
                    error: true,
                    message: $.mage.__(
                        'A technical problem with the server created an error. ' +
                        'Try again to continue what you were doing. If the problem persists, try again later.'
                    ),

                    /**
                     * @param {String} message
                     */
                    insertMethod: function (message) {
                        var $wrapper = $('<div></div>').html(message);

                        $('.page-main-actions').after($wrapper);
                    }
                });
        }
    });

    /**
     * Bootstrap application.
     */
    bootstrap = function () {
        /**
         * Init all components defined via data-mage-init attribute
         * and subscribe init action on contentUpdated event
         */
        mage.apply();

        /*
         * Initialization of notification widget
         */
        notification({}, $('body'));
    };

    $(bootstrap);
});
