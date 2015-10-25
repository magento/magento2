/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'jquery/jquery-storageapi'
], function ($) {
    'use strict';

    return function (config) {
        var translationStorage = $.initNamespaceStorage('mage-translation-storage').localStorage;
        var translationStorageInvalidation = $.initNamespaceStorage('mage-translation-timeout').localStorage;
        var timeout = $.localStorage.get('mage-translation-timeout');
        if ((timeout.timestamp != config.timestamp) || (!timeout.timestamp)) {
            require([config.config], function (string) {
                if (string.length) {
                    $.mage.translate.add(JSON.parse(string));
                    translationStorage.removeAll();
                    translationStorageInvalidation.removeAll();
                    $.localStorage.set('mage-translation-storage', string);
                    $.localStorage.set('mage-translation-timeout', {timestamp: config.timestamp});
                }
            });
        } else {
            $.mage.translate.add($.localStorage.get('mage-translation-storage'));
        }
    };
});
