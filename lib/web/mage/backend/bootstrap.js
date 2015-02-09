/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true browser:true */
/*global FORM_KEY:true*/
define([
    "jquery",
    "mage/apply/main",
    "mage/mage"
], function($, mage){
    'use strict';

    $.ajaxSetup({
        /*
         * @type {string}
         */
        type: 'POST',

        /*
         * Ajax before send callback
         * @param {Object} The jQuery XMLHttpRequest object returned by $.ajax()
         * @param {Object}
         */
        beforeSend: function(jqXHR, settings) {
            var form_key = typeof FORM_KEY !== 'undefined' ? FORM_KEY : null;
            if (!settings.url.match(new RegExp('[?&]isAjax=true',''))) {
                settings.url = settings.url.match(
                    new RegExp('\\?',"g")) ?
                    settings.url + '&isAjax=true' :
                    settings.url + '?isAjax=true';
            }
            if (!settings.data) {
                settings.data = {
                    form_key: form_key
                };
            } else if ($.type(settings.data) === "string" &&
                settings.data.indexOf('form_key=') === -1) {
                settings.data += '&' + $.param({
                    form_key: form_key
                });
            } else if($.isPlainObject(settings.data) && !settings.data.form_key) {
                settings.data.form_key = form_key;
            }
        },

        /*
         * Ajax complete callback
         * @param {Object} The jQuery XMLHttpRequest object returned by $.ajax()
         * @param {string}
         */
        complete: function(jqXHR) {
            if (jqXHR.readyState === 4) {
                try {
                    var jsonObject = $.parseJSON(jqXHR.responseText);
                    if (jsonObject.ajaxExpired && jsonObject.ajaxRedirect) {
                        window.location.replace(jsonObject.ajaxRedirect);
                    }
                } catch(e) {}
            }
        }
    });

    var bootstrap = function() {
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
