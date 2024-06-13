/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
/**
 * Coupon model.
 */
define([
    'ko',
    'domReady!'
], function (ko) {
    'use strict';

    var couponCode = ko.observable(null),
        isApplied = ko.observable(null);

    return {
        couponCode: couponCode,
        isApplied: isApplied,

        /**
         * @return {*}
         */
        getCouponCode: function () {
            return couponCode;
        },

        /**
         * @return {Boolean}
         */
        getIsApplied: function () {
            return isApplied;
        },

        /**
         * @param {*} couponCodeValue
         */
        setCouponCode: function (couponCodeValue) {
            couponCode(couponCodeValue);
        },

        /**
         * @param {Boolean} isAppliedValue
         */
        setIsApplied: function (isAppliedValue) {
            isApplied(isAppliedValue);
        }
    };
});
