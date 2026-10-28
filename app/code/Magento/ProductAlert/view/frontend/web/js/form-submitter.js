/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (data, element) {

        $(element).trigger('submit');
    };
});
