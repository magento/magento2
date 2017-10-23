/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/apply/main',
    'Magento_Ui/js/lib/knockout/bootstrap'
], function ($, mage) {
    'use strict';

    $.ajaxSetup({
        cache: false
    });

    /**
     * Init all components defined via data-mage-init attribute.
     */
    $(mage.apply);
});
