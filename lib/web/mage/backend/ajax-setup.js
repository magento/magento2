/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true */
/*global window:true FORM_KEY:true SessionError:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    
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
        beforeSend: function(jqXHR, settings){
            if (!settings.url.match(new RegExp('[?&]isAjax=true',''))) {
                settings.url = settings.url.match(
                    new RegExp('\\?',"g")) ?
                    settings.url + '&isAjax=true' :
                    settings.url + '?isAjax=true';
            }
            if ($.type(settings.data) === "string" && settings.data.indexOf('form_key=') === -1
                ) {
                settings.data += '&' + $.param({
                    form_key: FORM_KEY
                });
            } else {
                if (!settings.data) {
                    settings.data = {
                        form_key: FORM_KEY
                    };
                }
                if (!settings.data.form_key) {
                    settings.data.form_key = FORM_KEY;
                }
            }
        },
        /*
         * Ajax complete callback
         * @param {Object} The jQuery XMLHttpRequest object returned by $.ajax()
         */
        complete: function(jqXHR){
            if (jqXHR.readyState === 4) {
                try {
                    var jsonObject = $.parseJSON(jqXHR.responseText);
                    if (jsonObject.ajaxExpired && jsonObject.ajaxRedirect) {
                        window.location.replace(jsonObject.ajaxRedirect);
                        throw new SessionError('session expired');
                    }
                } catch (e) {
                    if (e instanceof SessionError) {
                        return;
                    }
                }
            }
        }
    });
});
