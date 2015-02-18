/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/apply/main'
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
