/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/html'
], function (Html) {
    'use strict';

    return Html.extend({
        defaults: {
            form: '${ $.namespace }.${ $.namespace }',
            visible: false,
            imports: {
                responseData: '${ $.form }:responseData',
                visible: 'responseData.error',
                content: 'responseData.messages'
            },
            listens: {
                '${ $.provider }:data.reset': 'hide'
            }
        },

        /**
         * Show messages.
         */
        show: function () {
            this.visible(true);
        },

        /**
         * Hide messages.
         */
        hide: function () {
            this.visible(false);
        }
    });
});
