/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
