/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return function () {
        $.jstree._themes = require.s.contexts._.config.baseUrl + 'jquery/jstree/themes/';
    };
});
