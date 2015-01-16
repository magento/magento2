/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
    $.widget('mage.shoppingCart', {
        _create: function() {
            if ($(this.options.updateCartActionContainer).length > 0) { /* <!--[if lt IE 8]> Only */
                $(this.options.emptyCartButton).on('click', $.proxy(function() {
                    $(this.options.emptyCartButton).attr('name', 'update_cart_action_temp');
                    $(this.options.updateCartActionContainer)
                        .attr('name', 'update_cart_action').attr('value', 'empty_cart');
                }, this));
            }
            $(this.options.continueShoppingButton).on('click', $.proxy(function() {
                location.href = this.options.continueShoppingUrl;
            }, this));
        }
    });

    return $.mage.shoppingCart;
});