/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return function () {
        var ctx = 's' in require ? require.s.contexts._ : require.contexts._;

        $.jstree._themes = ctx.config.baseUrl + 'jquery/jstree/themes/';
    };
});
