/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('marketing.ratingControl', {
        options: {
            colorFilled: '#333',
            colorUnfilled: '#CCCCCC',
            colorHover: '#f30'
        },

        /** @inheritdoc */
        _create: function () {
            this._labels = this.element.find('label');
            this._bind();
        },

        /**
         * @private
         */
        _bind: function () {
            this._labels.on({
                click: $.proxy(function (e) {
                    $('[id="' + $(e.currentTarget).attr('for') + '"]').prop('checked', true);
                    this._updateRating();
                }, this),

                hover: $.proxy(function (e) {
                    this._updateHover($(e.currentTarget), this.options.colorHover);
                }, this),

                mouseleave: $.proxy(function (e) {
                    this._updateHover($(e.currentTarget), this.options.colorUnfilled);
                }, this)
            });

            this._updateRating();
        },

        /**
         * @param {jQuery} elem
         * @param {String} color
         * @private
         */
        _updateHover: function (elem, color) {
            elem.nextAll('label').addBack().filter(function () {
                return !$(this).data('checked');
            }).css('color', color);
        },

        /**
         * @private
         */
        _updateRating: function () {
            var checkedInputs = this.element.find('input[type="radio"]:checked');

            checkedInputs.nextAll('label').addBack().css('color', this.options.colorFilled).data('checked', true);
            checkedInputs.prevAll('label').css('color', this.options.colorUnfilled).data('checked', false);
        }
    });

});
