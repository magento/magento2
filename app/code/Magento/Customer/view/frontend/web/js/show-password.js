/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/

define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        passwordSelector: '',
        showPasswordSelector: '[data-role=show-password]',
        passwordInputType: 'password',
        textInputType: 'text',

        defaults: {
            template: 'Magento_Customer/show-password'
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
        },

        /**
         * @return {Object}
         */
        initObservable: function () {
            var self = this;

            this._super()
                .observe({
                    isChecked: ko.observable(false)
                });

            this.isChecked.subscribe(function () {
                self._showPassword();
            });

            return this;
        },

        /**
         * Show/Hide password
         * @private
         */
        _showPassword: function () {
            var passwordField = this.passwordSelector;

            $(passwordField).attr('type',
                $(passwordField).attr('type') === this.passwordInputType ?
                this.textInputType : this.passwordInputType
            );
        }
    });
});
