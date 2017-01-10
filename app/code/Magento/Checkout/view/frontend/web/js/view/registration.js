/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Ui/js/model/messageList'
    ],
    function ($, Component, messageList) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/registration',
                accountCreated: false,
                creationStarted: false,
                isFormVisible: true
            },

            /**
             * Initialize observable properties
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
             * Create new user account
             */
            createAccount: function () {
                this.creationStarted(true);
                $.post(
                    this.registrationUrl
                ).done(
                    function (response) {

                        if (response.errors == false) {
                            this.accountCreated(true)
                        } else {
                            messageList.addErrorMessage(response);
                        }
                        this.isFormVisible(false);
                    }.bind(this)
                ).fail(
                    function (response) {
                        this.accountCreated(false)
                        this.isFormVisible(false);
                        messageList.addErrorMessage(response);
                    }.bind(this)
                );
            }
        });
    }
);
