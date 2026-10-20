/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'Magento_Review/js/process-reviews'
], function ($, processReviews) {
    'use strict';

    describe('Magento_Review/js/process-reviews', function () {
        var originalUrl = window.location.href,
            config = {
                productReviewUrl: 'review/product/listAjax/id/1/',
                reviewsTabSelector: '#tab-label-reviews'
            },
            reviewsHtml = '<div class="pages"><a href="review/product/listAjax/id/1/?p=2">2</a></div>',
            $container;

        beforeEach(function () {
            $container = $(
                '<div class="product data items">' +
                    '<div id="tab-label-reviews" data-role="collapsible" role="tab" class="active"></div>' +
                    '<div id="reviews" data-role="content">' +
                        '<div id="product-review-container" data-role="product-review"></div>' +
                    '</div>' +
                '</div>'
            ).appendTo('body');

            spyOn($, 'ajax').and.callFake(function () {
                return $.Deferred().resolve(reviewsHtml).promise();
            });
            spyOn($.fn, 'animate');
            spyOn($.fn, 'tabs');
        });

        afterEach(function () {
            $container.remove();
            window.history.replaceState(null, '', originalUrl);
        });

        it('loads reviews for an active reviews tab without scrolling on a #reviews anchor', function () {
            window.history.replaceState(null, '', window.location.pathname + window.location.search + '#reviews');

            processReviews(config);

            expect($.ajax).toHaveBeenCalledTimes(1);
            expect($.ajax.calls.mostRecent().args[0].url).toBe(config.productReviewUrl);
            expect($.fn.animate).not.toHaveBeenCalled();
            expect($.fn.tabs).not.toHaveBeenCalled();
        });

        it('scrolls to the reviews on pagination without re-activating the reviews tab', function () {
            processReviews(config);
            $container.find('.pages a').trigger('click');

            expect($.ajax).toHaveBeenCalledTimes(2);
            expect($.ajax.calls.mostRecent().args[0].url).toBe('review/product/listAjax/id/1/?p=2');
            expect($.fn.animate).toHaveBeenCalledTimes(1);
            expect($.fn.animate.calls.mostRecent().args[0].scrollTop).toEqual(jasmine.any(Number));
            expect($.fn.tabs).not.toHaveBeenCalled();
        });

        it('defers loading reviews until an inactive reviews tab opens', function () {
            $container.find('#tab-label-reviews').removeClass('active');

            processReviews(config);
            expect($.ajax).not.toHaveBeenCalled();

            $container.find('#tab-label-reviews').trigger('beforeOpen');
            expect($.ajax).toHaveBeenCalledTimes(1);
            expect($.fn.animate).not.toHaveBeenCalled();
        });
    });
});
