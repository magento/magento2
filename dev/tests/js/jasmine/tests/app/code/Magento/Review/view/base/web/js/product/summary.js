/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/columns/column',
    'mage/translate'
], function (_, Column, $t) {
    'use strict';

    return Column.extend({
        defaults: {
            ratingAmount: 0,
            reviewCount: 0,
            reviewsCountText: $t('Review'),
            maxRatingAmount: 100,
            showReviewsActions: false,
            showViewReviewAction: false,
            showAddReviewAction: false,
            showEmpty: false,
            bodyTmpl: 'Magento_Review/product/summary'
        },

        /**
         * Mock function
         *
         * @returns {Boolean}
         */
        getRating: function () {
            return false;
        },

        /**
         * Mock function
         *
         * @returns {Boolean}
         */
        hasRating: function () {
            return false;
        },

        /**
         * Mock function
         *
         * @returns {Boolean}
         */
        hasReviews:  function () {
            return false;
        }
    });
});
