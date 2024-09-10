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

    const idleCallback = window.requestIdleCallback ?? setTimeout;

    /**
     * Init all components defined via data-mage-init attribute.
     * Execute in a separate task to prevent main thread blocking.
     */
    idleCallback(mage.apply);
});
