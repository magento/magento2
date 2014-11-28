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
    "use strict";

    $.widget('mage.compareList', {
        _create: function() {

            var elem = this.element,
                products = $('thead td', elem);

            if (products.length > this.options.productsInRow) {
                var headings = $('<table/>')
                    .addClass('comparison headings data table')
                    .insertBefore(elem.closest('.container'));
                elem.addClass('scroll');

                $('th', elem).each(function(){
                    var th = $(this),
                        thCopy = th.clone();

                    th.animate({
                        top: '+=0'
                    }, 50, function(){
                        var height;
                        if ($.browser.mozilla && $.browser.version <= '11.0') {
                            height = th.outerHeight();
                        }
                        else {
                            height = th.height();
                        }
                        thCopy.css('height', height)
                            .appendTo(headings)
                            .wrap('<tr />');
                    });
                });
            }

            $(this.options.windowPrintSelector).on('click', function(e) {
                e.preventDefault();
                window.print();
            });

            $.each(this.options.selectors, function(i, selector) {
                $(selector).on('click', function(e) {
                    e.preventDefault();
                    window.location.href = $(this).data('url');
                });
            });

        }
    });

    return $.mage.compareList;
});