/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'jquery/jquery-storageapi'
], function ($) {
    'use strict';

    return function (pageOptions) {
        var dependencies = [],
            versionObj;

        $.initNamespaceStorage('mage-translation-storage');
        $.initNamespaceStorage('mage-translation-file-version');
        versionObj = $.localStorage.get('mage-translation-file-version');

        if (versionObj.version !== pageOptions.version) {
            dependencies.push(
              pageOptions.dictionaryFile
            );
        }

        require.config({
            deps: dependencies,

            /**
             * @param {String} string
             */
            callback: function (string) {
                if (typeof string === 'string') {
                    $.mage.translate.add(JSON.parse(string));
                    $.localStorage.set('mage-translation-storage', string);
                    $.localStorage.set(
                        'mage-translation-file-version',
                        {
                            version: pageOptions.version
                        }
                    );
                } else {
                    $.mage.translate.add($.localStorage.get('mage-translation-storage'));
                }
            }
        });
    };
});
