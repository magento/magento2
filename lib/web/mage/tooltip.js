/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    //Widget Wrapper
    $.widget('mage.tooltip', $.ui.tooltip, {});

    return $.mage.tooltip;
});
