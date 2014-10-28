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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    
    $.widget('mage.sticky', {
        options: {
            container: ''
        },

        /**
         * Bind handlers to scroll event
         * @private
         */
        _create: function() {
            $(window).on({
                'scroll': $.proxy(this._stick, this),
                'resize': $.proxy(this.reset, this)
            });

            this.element.on('dimensionsChanged', $.proxy(this.reset, this));

            this.reset();
        },

        /**
         * float Block on windowScroll
         * @private
         */
        _stick: function() {
            var offset,
                isStatic;

            isStatic = this.element.css('position') === 'static';

            if( !isStatic && this.element.is(':visible') ) {
                offset = $(document).scrollTop() - this.parentOffset;

                offset = Math.max( 0, Math.min( offset, this.maxOffset) );
                
                this.element.css( 'top', offset );
            }
        },

        /**
         * Defines maximum offset value of the element. 
         * @private
         */
        _calculateDimens: function(){
            var $parent         = this.element.parent(),
                topMargin       = parseInt( this.element.css("margin-top"), 10 ),
                parentHeight    = $parent.height() - topMargin,
                height          = this.element.innerHeight(),
                maxScroll       = document.body.offsetHeight - window.innerHeight;

            this.parentOffset   = $parent.offset().top + topMargin;
            this.maxOffset      = maxScroll - this.parentOffset;

            if( this.maxOffset + height >= parentHeight ){
                this.maxOffset = parentHeight - height;
            }

            return this;
        },

        /**
         * Facade method that palces sticky element where it should be.
         */
        reset: function(){
            this._calculateDimens()
                ._stick();
        }
    });

});
