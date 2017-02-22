/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['jquery', 'uiComponent'],
    function ($, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/registration',
                accountCreated: false,
                creationStarted: false
            },
            /** Initialize observable properties */
            initObservable: function () {
                this._super()
                    .observe('accountCreated')
                    .observe('creationStarted');
                return this;
            },
            getEmailAddress: function() {
                return this.email;
            },
            createAccount: function() {
                this.creationStarted(true);
                $.post(this.registrationUrl).done(
                    function() {
                        this.accountCreated(true)
                    }.bind(this)
                );
            }
        });
    }
);
