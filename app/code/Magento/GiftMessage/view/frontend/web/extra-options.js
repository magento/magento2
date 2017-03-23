/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.extraOptions', {
        options: {
            events: 'billingSave shippingSave',
            additionalContainer: '#onepage-checkout-shipping-method-additional-load'
        },

        /**
         * Set up event handler for requesting any additional extra options from the backend.
         * @private
         */
        _create: function () {
            this.element.on(this.options.events, $.proxy(this._addExtraOptions, this));
        },

        /**
         * Fetch the extra options using an Ajax call. Extra options include Gift Receipt and
         * Printed Card.
         * @private
         */
        _addExtraOptions: function () {
            $.ajax({
                url: this.options.additionalUrl,
                context: this,
                type: 'post',
                async: false,

                /** @inheritdoc */
                success: function (response) {
                    $(this.options.additionalContainer).html(response).trigger('contentUpdated');
                }
            });
        }
    });

    return $.mage.extraOptions;
});
