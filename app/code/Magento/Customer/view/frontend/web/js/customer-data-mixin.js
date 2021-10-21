/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    var mixin = {

        /**
         * Override the original function for aborting duplicate ajax requests to reload customer/section/load
         *
         * @param {Function} originFn - Original method.
         */
        onAjaxComplete: function (originFn) {
            try {
                originFn();
                this.ajaxPrefilterCall();
            } catch(err) {}
        },

        /**
         * Cancels duplicate ajax request to the server
         */
        ajaxPrefilterCall: function () {
            var currentRequests = {};

            $.ajaxPrefilter(function( newOptions, originalOptions, jqXHR ) {

                if ( currentRequests[ newOptions.url ] ) {
                    currentRequests[ newOptions.url ].abort();

                    // prevent duplicate ajax call for cart quantity
                    if (originalOptions.data.sections === 'messages') {
                        jqXHR.abort();
                    }
                }
                currentRequests[ newOptions.url ] = jqXHR;
            });
        }
    };

    /**
     * Override default customer-data.onAjaxComplete().
     */
    return function (target) {
        return wrapper.extend(target, mixin);
    };
});
