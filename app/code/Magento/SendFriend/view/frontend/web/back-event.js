/**
* Copyright © 2016 Magento. All rights reserved.
* See COPYING.txt for license details.
*/

define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        $(element).on('click', function () {
            history.back();

            return false;
        });
    };
});
