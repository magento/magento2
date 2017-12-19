/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.prolongCookie', {
        options: {
            prolongActionUrl: '',
        },

        /**
         * Widget constructor.
         * @private
         */
        _create: function () {
            this._prolongCookie();
        },

        /**
         * Prolongs cookie(s) lifetime by performing post ajax request.
         * @private
         */
        _prolongCookie: function () {
            if (this.options.prolongActionUrl.length === 0) {
                return;
            }

            $.post(this.options.prolongActionUrl);
        },
    });

    return $.mage.prolongCookie;
});
