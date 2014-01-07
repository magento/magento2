/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    frontend Persistent remember me popup
 * @package     mage
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function ($) {
    $.widget('mage.rememberMePopup', {
        options: {
            closeBtn: '.action.close',
            windowOverlayTemplate: '<div class="window overlay"></div>',
            popupBlockTemplate: '<div class="popup block remember tip active">'  +
                                '<span class="action close"></span>' +
                                '<div class="title">' +
                                    '<strong>${title}</strong>'+
                                '</div>' +
                                '<div class="content">' +
                                    '<p>${content}</p>' +
                                '</div>' +
                            '</div>'
        },

        _create: function() {
            this._renderWindowOverLay();
            this._renderPopupBlock();
            $('body').append(this.windowOverlay.hide());
            $('body').append(this.popupBlock.hide());
            this.element.find('a').on('click', $.proxy(this._showPopUp, this));
        },

        /**
         * Add windowOverlay block to body
         * If windowOverlay is not an option, use default template
         * @private
         */
        _renderWindowOverLay: function() {
            if (this.options.windowOverlay) {
                this.windowOverlay = $(this.options.windowOverlay);
            } else {
                $.template('windowOverlayTemplate', this.options.windowOverlayTemplate);
                this.windowOverlay = $.tmpl('windowOverlayTemplate').hide();
            }
            this.windowOverlay.height($('body').height());
        },

        /**
         * Add popupBlock to body
         * If popupBlock is not an option, use default template
         * @private
         */
        _renderPopupBlock: function() {
            if (this.options.popupBlock) {
                this.popupBlock = $(this.options.popupBlock);
            } else {
                $.template('popupBlockTemplate', this.options.popupBlockTemplate);
                this.popupBlock = $.tmpl('popupBlockTemplate',
                    {title: this.options.title, content: this.options.content});
            }
            this.popupBlock.find(this.options.closeBtn).on('click', $.proxy(this._hidePopUp, this));
        },

        /**
         * show windowOverlay and popupBlock
         * @private
         */
        _showPopUp: function() {
            this.windowOverlay.show();
            this.popupBlock.show();
            return false;
        },

        /**
         * hide windowOverlay and popupBlock
         * @private
         */
        _hidePopUp: function() {
            this.windowOverlay.hide();
            this.popupBlock.hide();
        }
    });
})(jQuery);