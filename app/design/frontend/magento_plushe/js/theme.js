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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

;
(function($) {
    'use strict';

    function listScroll() {
        var list = $('[data-action="scroll"]').addClass('carousel');
        var listInner = $('> .minilist.items', list);
        var items = $('.item', list);
        var itemWidth = $(items).length ? $(items[0]).outerWidth() : null;
        var perpage = (itemWidth !== null) ? Math.floor(list.outerWidth()/itemWidth) : null;
        var pages = (perpage !== null) ? Math.floor(items.length/perpage) : null;
        var page=0;
        for (var i=0 ; i < perpage; i++) {
            $(items[i + page*perpage]).addClass('shown');
        };
        for (var i=perpage; i < items.length; i++) {
            $(items[i + page*perpage]).addClass('hidden');
        };
        if ( itemWidth*items.length > list.outerWidth() ) {
                var next = $('<button class="action next" type="button"><span>Next</span></button>');
                var previous = $('<button class="action previous" type="button"><span>Previous</span></button>').attr('disabled', 'disabled');
                list.append(previous);
                list.append(next);
                listInner.wrap('<div class="items-wrapper" />');
                $('.items-wrapper').css('width', itemWidth*perpage);
                next.on('click.itemsScroll', function() {
                            list.addClass('animation');
                            items.removeClass('shown');
                            items.removeClass('hidden');
                            listInner.animate({
                            left: '-=' + itemWidth*perpage,
                        }, 400, 'easeInOutCubic', function() {
                            // Animation complete.
                            page = page + 1;
                            for (var i=0 ; i < perpage; i++) {
                                $(items[i + page*perpage]).addClass('shown');
                            };
                            for (var i=perpage; i < items.length; i++) {
                                $(items[i + page*perpage]).addClass('hidden');
                            };
                            console.log(i);
                            previous.removeAttr('disabled');
                            if (page == pages) {
                                next.attr('disabled', 'disabled');
                            }
                            list.removeClass('animation');
                        });
                    });
                previous.on('click.itemsScroll', function() {
                            list.addClass('animation');
                            items.removeClass('shown');
                            items.removeClass('hidden');
                            listInner.animate({
                            left: '+=' + itemWidth*perpage,
                        }, 400, 'easeInOutCubic', function() {
                            // Animation complete.
                            page = page - 1;
                            for (var i=0 ; i < perpage; i++) {
                                $(items[i + page*perpage]).addClass('shown');
                            };
                            for (var i=perpage; i < items.length; i++) {
                                $(items[i + page*perpage]).addClass('hidden');
                            };
                            next.removeAttr('disabled');
                            if (page == 0) {
                                previous.attr('disabled', 'disabled');
                            }
                            list.removeClass('animation');
                        });
                    });

        }
    }

    $(document).ready(function() {
        listScroll();

        if ($('body').hasClass('checkout-cart-index')) {
            $('.cart.summary > .block > .title').dropdown({autoclose:false, menu:'.title + .content'});
            if ($('#co-shipping-method-form .fieldset.rates').length > 0 && $('#co-shipping-method-form .fieldset.rates :checked').length === 0 ) {
                $('.block.shipping > .title').addClass('active');
                $('.block.shipping').addClass('active');
            }
        }

        if ($('[role="navigation"]').length) {
            $('[role="navigation"]').navigationMenu({
                responsive: true,
                submenuContiniumEffect: true
            });
        } else {
            $('<nav class="navigation" role="navigation"></nav>').navigationMenu({
                responsive: true,
                submenuContiniumEffect: true
            });
        }

    });

})(window.jQuery);