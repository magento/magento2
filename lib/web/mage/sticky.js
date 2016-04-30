/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
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

            if(this.options.container.length > 0) {
               maxScroll = $(this.options.container).height();
            }

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
    
    return $.mage.sticky;
});
