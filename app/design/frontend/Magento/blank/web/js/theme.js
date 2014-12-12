/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/collapsible',
    'mage/ie-class-fixer',
    'jquery/ui'
],function($, keyboardHandler) {
    'use strict';

    $(function() {

        if ($('body').hasClass('checkout-cart-index')) {
            if ($('#co-shipping-method-form .fieldset.rates').length > 0 && $('#co-shipping-method-form .fieldset.rates :checked').length === 0 ) {
                $('#block-shipping').on('collapsiblecreate' ,function() {
                    $('#block-shipping').collapsible('forceActivate');
                });
            }
        }

        $('.cart-summary').mage('sticky', {
            container: '#maincontent'
        });

        $('.panel.header > .header.links').clone().appendTo('#store\\.links');

        keyboardHandler.apply();
    });

});
