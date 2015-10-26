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
        var translationStorage = $.initNamespaceStorage('mage-translation-storage').localStorage,
            translationStorageInvalidation = $.initNamespaceStorage('mage-translation-file-version').localStorage,
            timeout = $.localStorage.get('mage-translation-file-version');

        if (timeout.version !== config.version || !timeout.version) {
            require([config.config], function (string) {
                if (string.length) {
                    $.mage.translate.add(JSON.parse(string));
                    translationStorage.removeAll();
                    translationStorageInvalidation.removeAll();
                    $.localStorage.set('mage-translation-storage', string);
                    $.localStorage.set(
                        'mage-translation-file-version',
                        {
                            version: config.version
                        }
                    );
                }
            });
        } else {
            $.mage.translate.add($.localStorage.get('mage-translation-storage'));
        }
    };
});
