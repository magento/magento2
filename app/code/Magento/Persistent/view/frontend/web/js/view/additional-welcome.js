/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'Magento_Customer/js/customer-data'
], function ($, $t, customerData) {
    'use strict';

    return {
        /**
         * Init
         */
        init: function () {
            var persistent = customerData.get('persistent');

            if (persistent().fullname === undefined) {
                customerData.get('persistent').subscribe(this.replacePersistentWelcome);
            } else {
                this.replacePersistentWelcome();
            }
        },

        /**
         * Replace welcome message for customer with persistent cookie.
         */
        replacePersistentWelcome: function () {
            var persistent = customerData.get('persistent'),
                welcomeElems;

            if (persistent().fullname !== undefined) {
                welcomeElems = $('li.greet.welcome > span.not-logged-in');

                if (welcomeElems.length) {
                    $(welcomeElems).each(function () {
                        var html = $t('Welcome, %1!').replace('%1', persistent().fullname);

                        $(this).attr('data-bind', html);
                        $(this).html(html);
                    });
                }
            }
        },

        /**
         * @constructor
         */
        'Magento_Persistent/js/view/additional-welcome': function () {
            this.init();
        }
    };
});
