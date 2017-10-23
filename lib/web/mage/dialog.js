/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @deprecated since version 2.2.0
 */
define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    /**
     * Dialog Widget - this widget is a wrapper for the jQuery UI Dialog
     */
    $.widget('mage.dialog', $.ui.dialog, {});

    return $.mage.dialog;
});
