/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
/* eslint-disable max-nested-callbacks, no-undef */
define([
    'jquery',
    'Magento_Theme/js/model/breadcrumb-list',
    'mage/translate'
], function ($, breadcrumbList) {
    'use strict';

    /**
     * @return {Object}
     */
    var homeCrumb = function () {
        return {
            name: 'home',
            label: $.mage.__('Home'),
            title: $.mage.__('Go to Home Page'),
            link: BASE_URL || ''
        };
    };

    return function (breadcrumb) {

        breadcrumbList.unshift(homeCrumb());

        return breadcrumb;
    };
});
