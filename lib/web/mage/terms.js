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
define([
    "jquery"
], function($){

    'use strict';

    $.fn.terms = function(args){

        // default
        var defaults = {
            start:0,
            wrapper:'',
            showAnchor:'',
            effects:'slide'
        };

        var options = $.extend(defaults, args);

        this.each(function() {
            var obj = $(this),
                wrapper = (options.wrapper !== '') ? '> ' + options.wrapper : '',
                switches = $(wrapper + '> [data-section="title"] > [data-toggle="switch"]',obj),
                terms = $(wrapper + '> [data-section="content"]',obj),
                t = switches.length,
                marginTop = $(switches[0]).closest('[data-section="title"]').css('position') == 'absolute' ? 0 : null,
                title,
                current,

                init = function() {
                    if (t > 0) {
                        if($(switches[0]).closest('[data-section="title"]').css('display')=='table-cell') {
                            obj.addClass('adjusted');
                            var linksList;
                            if (obj[0].tagName=='DL') {
                                linksList = $('<dd>');
                            } else {
                                linksList = $('<div>');
                            }
                            linksList.addClass('sections-nav');
                            obj.prepend(linksList);

                            for (var i=0; i < t; i++) {
                                title = $(switches[i]).html();
                                var classes = $(switches[i]).closest('[data-section="title"]').attr('class');
                                var dataSection = $(switches[i]).closest('[data-section="title"]').attr('data-section');
                                var itemHref = $(switches[i]).attr('href');
                                var itemClass = $(switches[i]).attr('class');
                                $(switches[i]).parent('[data-section="title"]').hide();
                                switches[i] = $('<a/>',{
                                    href: itemHref,
                                    'class' : itemClass,
                                    html: title
                                }).appendTo(linksList);
                                $(switches[i]).wrap('<strong class="'+classes+'" data-section="'+dataSection+'" />');
                            }
                        }
                        $(switches).each(function(ind,el){
                            $(el).click(function(event){
                                event.preventDefault();
                                showItem(ind);
                            });
                            if (marginTop !== null) {
                                $(el).closest('[data-section="title"]').css({'top' : marginTop + 'px'});
                                marginTop = marginTop + $(el).closest('[data-section="title"]').outerHeight(true);
                                obj.css({'min-height' : marginTop + 'px' });
                            }
                        });

                        var fromUrl = false;
                        if (window.location.hash.length > 0) {
                            $(terms).each(function(ind,el) {
                                if ( '#info-'+$(el).attr('id') == window.location.hash) {
                                    showItem(ind);
                                    $('html, body').animate({
                                        scrollTop: $(switches[ind]).offset().top
                                    }, 700);
                                    fromUrl = true;
                                }
                            });
                        }
                        if (fromUrl === false) {
                            if ( options.start % 1 === 0 ) {
                                current = options.start + 1;
                                showItem(options.start);
                            } else {
                                $(terms).each(function(ind,el) {
                                    if ( $(el).attr('id') == options.start) {
                                        current = ind + 1;
                                        showItem(ind);
                                        $('html, body').animate({
                                            scrollTop: $(switches[ind]).offset().top
                                        }, 700);
                                    }
                                });
                            }
                        }
                    }
                },


                showItem = function(item) {
                    if (item != current && !$(switches[item]).closest('[data-section="title"]').hasClass('disabled') ) {
                        $(switches).closest('[data-section="title"]').removeClass('active');
                        if (options.wrapper !== '') {
                            $(switches).parent().parent().removeClass('active');
                        }
                        $(terms).removeClass('active');
                        $(switches[item]).closest('[data-section="title"]').addClass('active');
                        if (options.wrapper !== '') {
                            $(switches[current]).parent().parent().addClass('active');
                        }
                        $(terms[item]).addClass('active');

                        /*if ($(terms[item]).attr('id')) {
                         scr = document.body.scrollTop;
                         window.location.hash='#tab-' + $(terms[item]).attr('id');
                         document.body.scrollTop = scr;
                         }*/
                        current = item;
                    } else if (
                    // Check if this is accordion width as criteria for now
                        (obj.attr('data-sections') == 'accordion' ||
                            $(switches[item]).closest('[data-section="title"]').css('width') == obj.css('width')
                            ) &&
                            item == current && !$(switches[item]).closest('[data-section="title"]').hasClass('disabled')
                        ) {
                        $(switches).closest('[data-section="title"]').removeClass('active');
                        if (options.wrapper !== '') {
                            $(switches).parent().parent().removeClass('active');
                        }
                        $(terms).removeClass('active');
                        current = -1;
                    }
                };

            init();
        });
    };
    $(document).ready(function() {
        $('[data-sections]').terms();
    });

});
