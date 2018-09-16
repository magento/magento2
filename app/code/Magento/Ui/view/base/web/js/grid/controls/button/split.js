/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (data, element) {

        $(element).on('click.splitDefault', '.action-default', function () {
            $(this).siblings('.dropdown-menu').find('.item-default').trigger('click');
        });
    };
});
