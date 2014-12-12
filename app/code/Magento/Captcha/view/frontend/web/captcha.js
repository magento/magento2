/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
    $.widget('mage.captcha', {
        options: {
            refreshClass: 'refreshing',
            reloadSelector: '.captcha-reload',
            imageSelector: '.captcha-img'
        },

        /**
         * Method binds click event to reload image
         * @private
         */
        _create: function() {
            this.element.on('click', this.options.reloadSelector, $.proxy(this.refresh, this));
        },

        /**
         * Method triggeres an AJAX request to refresh the CAPTCHA image
         * @param e - Event
         */
        refresh: function(e) {
            var reloadImage = $(e.currentTarget);
            reloadImage.addClass(this.options.refreshClass);
            $.ajax({
                url: this.options.url,
                type: 'post',
                async: false,
                dataType: 'json',
                context: this,
                data: {
                    'formId': this.options.type
                },
                success: function (response) {
                    if (response.imgSrc) {
                        this.element.find(this.options.imageSelector).attr('src', response.imgSrc);
                    }
                },
                complete: function() {
                    reloadImage.removeClass(this.options.refreshClass);
                }
            });
        }
    });

    return $.mage.captcha;
});