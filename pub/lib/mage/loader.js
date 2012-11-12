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
 * @category    Mage
 * @package     mage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true */
(function($){
    $.widget("mage.loader", {
        options: {
            icon: '',
            texts: {
                loaderText: 'Please wait...',
                imgAlt: 'Loading...'
            },
            template: '<div class="loading-mask"><p class="loader">'+
                '<img {{if texts.imgAlt}}alt="${texts.imgAlt}"{{/if}} src="${icon}"><br>'+
                '<span>{{if texts.loaderText}}${texts.loaderText}{{/if}}</span></p></div>'
        },
        /**
         * Loader creation
         * @protected
         */
        _create: function () {
            this._render();
            this._bind();
        },
        /**
         * Bind on ajax complete event
         * @protected
         */
        _bind: function(){
            this.element.on('ajaxComplete ajaxError', function(e){
                e.stopImmediatePropagation();
                $(e.target).loader('hide');

            });
        },
        /**
         * Show loader
         */
        show: function () {
            this.loader.show();
        },
        /**
         * Hide loader
         */
        hide: function () {
            this.loader.hide();
        },
        /**
         * Render loader
         * @protected
         */
        _render: function () {
            this.loader = $.tmpl(this.options.template, this.options)
                .css(this._getCssObj());
            if (this.element.is('body')) {
                this.element.prepend(this.loader);
            } else {
                this.element.before(this.loader);
            }
        },
        /**
         * Prepare object with css properties for loader
         * @protected
         */
        _getCssObj: function(){
            var isBodyElement = this.element.is('body'),
                width = isBodyElement ? $(window).width() : this.element.outerWidth(),
                height = isBodyElement ? $(window).height() : this.element.outerHeight(),
                position = isBodyElement ? 'fixed' : 'relative';
            return {
                height: height + 'px',
                width: width + 'px',
                position: position,
                'margin-bottom': '-' + height + 'px'
            };
        },
        /**
         * Destroy loader
         */
        destroy: function () {
            this.loader.remove();
            return $.Widget.prototype.destroy.call(this);
        }
    });
    $(document).ready(function(){
        $('body').on('ajaxSend', function(e){
            $(e.target).loader().loader('show');
        });
    });
})(jQuery);
