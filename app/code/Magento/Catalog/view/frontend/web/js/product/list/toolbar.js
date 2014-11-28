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
            /*this._bind($(this.options.modeControl), this.options.modeCookie);
            this._bind($(this.options.directionControl), this.options.directionCookie);
            this._bind($(this.options.orderControl), this.options.orderCookie);
            this._bind($(this.options.limitControl), this.options.limitCookie);*/
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