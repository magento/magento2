/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/cookies"
], function($){

    $.widget('mage.cookieBlock', {
        _create: function() {
            if ($.mage.cookies.get(this.options.cookieName)) {
                this.element.hide();
            } else {
                this.element.show();
            }
            $(this.options.cookieAllowButtonSelector).on('click', $.proxy(function() {
                var cookieExpires = new Date(new Date().getTime() + this.options.cookieLifetime * 1000);
                $.mage.cookies.set(this.options.cookieName, this.options.cookieValue, {expires: cookieExpires});
                if ($.mage.cookies.get(this.options.cookieName)) {
                    window.location.reload();
                } else {
                    window.location.href = this.options.noCookiesUrl;
                }
            }, this));
        }
    });

    return $.mage.cookieBlock;
});