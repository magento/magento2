/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
/*jshint browser:true jquery:true*/
/*global confirm:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/decorate"
], function($){

    $.widget('mage.sidebar', {
        options: {
            isRecursive: true
        },
        _create: function() {
            this.element.decorate('list', this.options.isRecursive);
            $(this.options.checkoutButton).on('click', $.proxy(function() {
                location.href = this.options.checkoutUrl;
            }, this));
            $(this.options.removeButton).on('click', $.proxy(function() {
                return confirm(this.options.confirmMessage);
            }, this));
        }
    });

    return $.mage.sidebar;
});