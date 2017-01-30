/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true expr:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
    $.widget('mage.setPassword', {
        options: {
        },

        _create: function() {
            this.element.on('change', $.proxy(function(event) {
                $(event.target).is(':checked') ? this._showPassword() : this._hidePassword();
            }, this));
            this.options.showOnDefault && this._showPassword();
        },

        /**
         * Hide password input fields
         * @private
         */
        _hidePassword: function() {
            $(this.options.currentPasswordId).removeAttr('data-validate')
                .parents(this.options.passwordContainer).hide();
            $(this.options.passwordId).removeAttr('data-validate');
            $(this.options.confirmationId).removeAttr('data-validate');
        },

        /**
         * Show password input fields
         * @private
         */
        _showPassword: function() {
            $(this.options.currentPasswordId).attr('data-validate', '{required:true}')
                .parents(this.options.passwordContainer).show();
            $(this.options.passwordId).attr('data-validate', "{required:true, 'validate-password':true}");
            $(this.options.confirmationId).attr('data-validate',
                '{required:true, equalTo:"' + this.options.passwordId + '"}');
        }
    });
    
    return $.mage.setPassword;
});
