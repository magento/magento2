/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui",
    "mage/dataPost",
    "jquery/jquery.cookie"
], function($){
    /**
     * ProductListToolbarForm Widget - this widget is setting cookie and submitting form according to toolbar controls
     */
    $.widget('mage.productListToolbarForm', {

        options: {
            modeControl: '[data-role="mode-switcher"]',
            directionControl: '[data-role="direction-switcher"]',
            orderControl: '[data-role="sorter"]',
            limitControl: '[data-role="limiter"]',
            modeCookie: 'product_list_mode',
            directionCookie: 'product_list_dir',
            orderCookie: 'product_list_order',
            limitCookie: 'product_list_limit',
            postData: {}
        },

        _create: function() {
            this._bind($(this.options.modeControl), this.options.modeCookie);
            this._bind($(this.options.directionControl), this.options.directionCookie);
            this._bind($(this.options.orderControl), this.options.orderCookie);
            this._bind($(this.options.limitControl), this.options.limitCookie);
        },

        _bind: function(element, cookieValue) {
            if (element.is("select")) {
                element.on('change', {cookieName: cookieValue}, $.proxy(this._processSelect, this));
            } else {
                element.on('click', {cookieName: cookieValue}, $.proxy(this._processLink, this));
            }
        },

        _processLink: function(event) {
            event.preventDefault();
            this._setCookie(event.data.cookieName, $(event.currentTarget).data('value'));
            $.mage.dataPost().postData(this.options.postData);
        },

        _processSelect: function(event) {
            this._setCookie(
                event.data.cookieName,
                event.currentTarget.options[event.currentTarget.selectedIndex].value
            );
            $.mage.dataPost().postData(this.options.postData);
        },

        _setCookie: function(cookieName, cookieValue) {
            $.cookie(cookieName, cookieValue, {path: '/'});
        }
    });
    

    return $.mage.productListToolbarForm;
});