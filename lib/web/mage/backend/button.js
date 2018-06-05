/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global require:true*/
(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'jquery/ui'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    'use strict';

    $.widget('ui.button', $.ui.button, {
        options: {
            eventData: {},
            waitTillResolved: true
        },

        /**
         * Button creation.
         * @protected
         */
        _create: function () {
            if (this.options.event) {
                this.options.target = this.options.target || this.element;
                this._bind();
            }

            this._super();
        },

        /**
         * Bind handler on button click.
         * @protected
         */
        _bind: function () {
            this.element
                .off('click.button')
                .on('click.button', $.proxy(this._click, this));
        },

        /**
         * Button click handler.
         * @protected
         */
        _click: function () {
            var options = this.options;

            $(options.target).trigger(options.event, [options.eventData]);
        }
    });

    return $.ui.button;
}));
