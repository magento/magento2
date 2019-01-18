/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery'
], function (Component, $) {
    'use strict';

    return Component.extend({
        /**
         * @returns {Object}
         */
        initialize: function () {
            this._super();

            return this.initEvents();
        },

        /**
         * @returns {Object}
         */
        initEvents: function () {
            $('a[data-action="' + this.linkDataAction + '"]').off('click.' + this.id)
                .on('click.' + this.id, this.click.bind(this));

            return this;
        },

        /**
         * @param {Object} event
         * @returns void
         */
        click: function (event) {
            event.preventDefault();

            $('#' + this.paypalButton).click();
        }
    });
});
