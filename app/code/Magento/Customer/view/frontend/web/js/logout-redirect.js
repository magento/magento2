/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    return function (data) {
        $($.mage.redirect(data.url, 'assign', 5000));
    };
});
