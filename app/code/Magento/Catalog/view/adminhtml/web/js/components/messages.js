/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
