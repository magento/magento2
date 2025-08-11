/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
