/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * JQuery UI Widget declaration: 'mage.truncateOptions'
 *
 * @deprecated since version 2.2.0
 */
define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.truncateOptions', {
        options: {
            detailsLink: 'a.details',
            mouseEvents: 'mouseover mouseout',
            truncatedFullValue: 'div.truncated.full.value'
        },

        /**
         * Establish the event handler for mouse events on the appropriate elements.
         *
         * @private
         */
        _create: function () {
            this.element.on(this.options.mouseEvents, $.proxy(this._toggleShow, this))
                .find(this.options.detailsLink).on(this.options.mouseEvents, $.proxy(this._toggleShow, this));
        },

        /**
         * Toggle the "show" class on the associated element.
         *
         * @private
         * @param {jQuery.Event} event - Mouse over/out event.
         */
        _toggleShow: function (event) {
            $(event.currentTarget).find(this.options.truncatedFullValue).toggleClass('show');
        }
    });
});
