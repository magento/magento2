/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'tabs',
    'collapsible'
], function ($) {
    'use strict';

    /**
     * @param {String} url
     * @param {*} fromPages
     */
    function processReviews(url, fromPages) {
        $.ajax({
            url: url,
            cache: true,
            dataType: 'html',
            showLoader: false,
            loaderContext: $('.product.data.items')
        }).done(function (data) {
            $('#product-review-container').html(data).trigger('contentUpdated');
            $('[data-role="product-review"] .pages a').each(function (index, element) {
                $(element).on('click', function (event) { //eslint-disable-line max-nested-callbacks
                    processReviews($(element).attr('href'), true);
                    event.preventDefault();
                });
            });
        }).always(function () {
            if (fromPages == true) { //eslint-disable-line eqeqeq
                $('html, body').animate({
                    scrollTop: $('#reviews').offset().top - 50
                }, 300);
            }
        });
    }

    return function (config) {
        var reviewTab = $(config.reviewsTabSelector),
            requiredReviewTabRole = 'tab';

        if (reviewTab.attr('role') === requiredReviewTabRole && reviewTab.hasClass('active')) {
            processReviews(config.productReviewUrl, location.hash === '#reviews');
        } else {
            reviewTab.one('beforeOpen', function () {
                processReviews(config.productReviewUrl);
            });
        }

        $(function () {
            $('.product-info-main .reviews-actions a').on('click', function (event) {
                var anchor, addReviewBlock;

                event.preventDefault();
                anchor = $(this).attr('href').replace(/^.*?(#|$)/, '');
                addReviewBlock = $('#' + anchor);

                if (addReviewBlock.length) {
                    $('.product.data.items [data-role="content"]').each(function (index) { //eslint-disable-line
                        if (this.id == 'reviews') { //eslint-disable-line eqeqeq
                            $('.product.data.items').tabs('activate', index);
                        }
                    });
                    $('html, body').animate({
                        scrollTop: addReviewBlock.offset().top - 50
                    }, 300);
                }

            });
        });
    };
});
