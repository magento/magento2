/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/model/messageList'
], function ($, Component, messageList) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/registration',
            accountCreated: false,
            creationStarted: false,
            isFormVisible: true
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super()
                .observe('accountCreated')
                .observe('isFormVisible')
                .observe('creationStarted');

            return this;
        },

        /**
         * @return {*}
         */
        getEmailAddress: function () {
            return this.email;
        },

        /**
         * @return String
         */
        getUrl: function () {
            return this.registrationUrl;
        },

        /**
         * Create new user account.
         *
         * @deprecated
         */
        createAccount: function () {
            this.creationStarted(true);
            $.post(
                this.registrationUrl
            ).done(
                function (response) {

                    if (response.errors == false) { //eslint-disable-line eqeqeq
                        this.accountCreated(true);
                    } else {
                        messageList.addErrorMessage(response);
                    }
                    this.isFormVisible(false);
                }.bind(this)
            ).fail(
                function (response) {
                    this.accountCreated(false);
                    this.isFormVisible(false);
                    messageList.addErrorMessage(response);
                }.bind(this)
            );
        }
    });
});
