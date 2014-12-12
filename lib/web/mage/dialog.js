/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    /**
     * Dialog Widget - this widget is a wrapper for the jQuery UI Dialog
     */
    $.widget('mage.dialog', $.ui.dialog, {});

    return $.mage.dialog;
});