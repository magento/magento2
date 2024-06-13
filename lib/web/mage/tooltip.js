/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/**
 * @deprecated since version 2.2.0
 */
define([
    'jquery',
    'jquery-ui-modules/tooltip'
], function ($) {
    'use strict';

    //Widget Wrapper
    $.widget('mage.tooltip', $.ui.tooltip, {});

    return $.mage.tooltip;
});
