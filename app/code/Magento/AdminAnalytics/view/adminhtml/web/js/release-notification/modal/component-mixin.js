/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

define(['jquery', 'analyticsPopupConfig'], function ($, analyticsPopupConfig) {
    'use strict'; // eslint-disable-line strict
    var deferred = $.Deferred(), mixin = {
        /**
         * Initializes content only if its visible
         */
        initializeContent: function () {
            var initializeContent = this._super.bind(this);

            if (!analyticsPopupConfig.analyticsVisible) {
                initializeContent();
            } else {
                deferred.then(function () {
                    initializeContent();
                });
            }
        },

        /**
         * Initializes release notification content after admin analytics
         */
        initializeContentAfterAnalytics: function () {
            deferred.resolve();
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
