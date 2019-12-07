/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
