/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
        var baseUrl = window.location.protocol + '//' + window.location.host + '/';

        return {
            name: 'home',
            label: $.mage.__('Home'),
            title: $.mage.__('Go to Home Page'),
            link: baseUrl
        };
    };

    return function (breadcrumb) {

        breadcrumbList.unshift(homeCrumb());

        return breadcrumb;
    };
});
