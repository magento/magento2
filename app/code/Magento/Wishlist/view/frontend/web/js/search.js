/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    $.widget('mage.wishlistSearch', {

        /**
         * Bind handlers to events
         */
        _create: function() {
            this.element.on('change', $.proxy(this._toggleForm, this));
        },

        /**
         * Toggle Form
         * @private
         */
        _toggleForm: function() {
            switch (this.element.val()) {
                case 'name':
                    $(this.options.emailFormSelector).hide();
                    $(this.options.nameFormSelector).show();
                    break;
                case 'email':
                    $(this.options.nameFormSelector).hide();
                    $(this.options.emailFormSelector).show();
                    break;
                default:
                    $(this.options.emailFormSelector).add(this.options.nameFormSelector).hide();
            }
        }
    });

    return $.mage.wishlistSearch;
});
