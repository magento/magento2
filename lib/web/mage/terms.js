/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * @param {*} args
     */
    $.fn.terms = function (args) {

        // default
        var defaults = {
                start: 0,
                wrapper: '',
                showAnchor: '',
                effects: 'slide'
            },
            options = $.extend(defaults, args);

        this.each(function () {
            var obj = $(this),
                wrapper = options.wrapper !== '' ? '> ' + options.wrapper : '',
                switches = $(wrapper + '> [data-section="title"] > [data-toggle="switch"]', obj),
                terms = $(wrapper + '> [data-section="content"]', obj),
                t = switches.length,
                marginTop = $(switches[0]).closest('[data-section="title"]').css('position') == 'absolute' ? 0 : null, //eslint-disable-line
                title,
                current,

                /**
                 * @param {*} item
                 */
                showItem = function (item) {
                    if (item != current && !$(switches[item]).closest('[data-section="title"]').hasClass('disabled')) { //eslint-disable-line
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
                        current = item;
                    } else if (
                        // Check if this is accordion width as criteria for now
                        (obj.attr('data-sections') == 'accordion' || $(switches[item]).closest('[data-section="title"]').css('width') == obj.css('width')) && //eslint-disable-line
                        item == current && !$(switches[item]).closest('[data-section="title"]').hasClass('disabled') //eslint-disable-line
                    ) {
                        $(switches).closest('[data-section="title"]').removeClass('active');

                        if (options.wrapper !== '') {
                            $(switches).parent().parent().removeClass('active');
                        }
                        $(terms).removeClass('active');
                        current = -1;
                    }
                },

                /**
                 * Init.
                 */
                init = function () {
                    var linksList, i, classes, dataSection, itemHref, itemClass, fromUrl;

                    if (t > 0) {
                        if ($(switches[0]).closest('[data-section="title"]').css('display') == 'table-cell') { //eslint-disable-line
                            obj.addClass('adjusted');

                            if (obj[0].tagName == 'DL') { //eslint-disable-line eqeqeq, max-depth
                                linksList = $('<dd>');
                            } else {
                                linksList = $('<div>');
                            }
                            linksList.addClass('sections-nav');
                            obj.prepend(linksList);

                            for (i = 0; i < t; i++) { //eslint-disable-line max-depth
                                title = $(switches[i]).html();
                                classes = $(switches[i]).closest('[data-section="title"]').attr('class');
                                dataSection = $(switches[i]).closest('[data-section="title"]').attr('data-section');
                                itemHref = $(switches[i]).attr('href');
                                itemClass = $(switches[i]).attr('class');
                                $(switches[i]).parent('[data-section="title"]').hide();
                                switches[i] = $('<a/>', {
                                    href: itemHref,
                                    'class': itemClass,
                                    html: title
                                }).appendTo(linksList);
                                $(switches[i]).wrap(
                                    '<strong class="' + classes + '" data-section="' + dataSection + '" />'
                                );
                            }
                        }
                        $(switches).each(function (ind, el) {
                            $(el).click(function (event) {
                                event.preventDefault();
                                showItem(ind);
                            });

                            if (marginTop !== null) {
                                $(el).closest('[data-section="title"]').css({
                                    'top': marginTop + 'px'
                                });
                                marginTop += $(el).closest('[data-section="title"]').outerHeight(true);
                                obj.css({
                                    'min-height': marginTop + 'px'
                                });
                            }
                        });

                        fromUrl = false;

                        if (window.location.hash.length > 0) {
                            $(terms).each(function (ind, el) {
                                if ('#info-' + $(el).attr('id') == window.location.hash) { //eslint-disable-line eqeqeq
                                    showItem(ind);
                                    $('html, body').animate({
                                        scrollTop: $(switches[ind]).offset().top
                                    }, 700);
                                    fromUrl = true;
                                }
                            });
                        }

                        if (fromUrl === false) {
                            if (options.start % 1 === 0) { //eslint-disable-line max-depth
                                current = options.start + 1;
                                showItem(options.start);
                            } else {
                                $(terms).each(function (ind, el) {
                                    if ($(el).attr('id') == options.start) { //eslint-disable-line eqeqeq
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
                };

            init();
        });
    };

    return function (data, el) {
        $(el).terms(data);
    };
});
