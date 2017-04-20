/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global FORM_KEY */
define([
    'jquery',
    'mage/apply/main',
    'Magento_Ui/js/lib/knockout/bootstrap',
    'mage/mage',
    'mage/translate'
], function ($, mage) {
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
            } else if ($.type(settings.data) === 'string' &&
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
                    jsonObject = $.parseJSON(jqXHR.responseText);

                    if (jsonObject.ajaxExpired && jsonObject.ajaxRedirect) { //eslint-disable-line max-depth
                        window.location.replace(jsonObject.ajaxRedirect);
                    }
                } catch (e) {}
            }
        },

        /**
         * Error callback.
         */
        error: function (jqXHR, status, error) {
            var message;

            switch (status) {
                case 'timeout':
                    message = $.mage.__('The request timed out.');
                    break;
                case 'notmodified':
                    message = $.mage.__('The request was not modified but was not retrieved from the cache.');
                    break;
                case 'parsererror':
                    message = $.mage.__('XML/Json format is bad.');
                    break;
                case 'abort':
                    message = $.mage.__('The request was aborted by the server.');
                    break;
                default:
                    message = $.mage.__('HTTP Error') + ' (' + jqXHR.status + ' ' + jqXHR.statusText + ').';
            }

            $('body').notification('clear')
                .notification('add', {
                    error: true,
                    message: message,

                    /**
                     * @param {*} message
                     */
                    insertMethod: function (message) {
                        var $wrapper = $('<div/>').addClass(this.messagesClass).html(message);

                        $('.page-main-actions', this.selectorPrefix).after($wrapper);
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
        $('body').mage('notification');
    };

    $(bootstrap);
});
